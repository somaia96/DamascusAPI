<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Http\Resources\ServiceResource;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\ServiceCategory;



class ApiServiceController extends Controller
{

    public function index(Request $request)
    {
        $limit = $request->query('limit', null);
        $serviceCategoryId = $request->query('service_category_id', null);

        $query = Service::latest();

        if ($serviceCategoryId) {
            $query->where('service_category_id', $serviceCategoryId);
        }

        if ($limit && is_numeric($limit)) {
            $query->limit($limit);
        }

        $services = $query->get();

        if ($services->isEmpty()) {
            return response()->json(['message' => 'No activities found'], 404);
        }


        return response()->json([
            'count' => $services->count(),
            'data' => ServiceResource::collection($services),
        ]);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'service_category_id' => 'required|exists:service_categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $service = Service::create([
            'title' => $request->title,
            'description' => $request->description,
            'service_category_id' => $request->service_category_id,
        ]);
        return response()->json(['message' => 'Service created successfully'], 201);
    }


    public function show(Service $service)
    {
        return new ServiceResource($service);
    }

    public function update(Request $request, $id)
    {
        try {
            // First find the service
            $service = Service::findOrFail($id);

            Log::info('Starting service update', [
                'service_id' => $id,
                'original_service' => $service->toArray(),
                'request_data' => $request->all()
            ]);

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'service_category_id' => 'required|exists:service_categories,id',
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed for service update', [
                    'service_id' => $id,
                    'errors' => $validator->errors()->toArray()
                ]);
                return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
            }

            // Check if service category exists
            $serviceCategory = ServiceCategory::find($request->service_category_id);
            if (!$serviceCategory) {
                Log::error('Service category not found', [
                    'service_category_id' => $request->service_category_id
                ]);
                return response()->json(['message' => 'Service category not found'], 404);
            }

            DB::beginTransaction();
            try {
                $updated = $service->update([
                    'title' => $request->title,
                    'description' => $request->description,
                    'service_category_id' => $request->service_category_id,
                ]);

                if (!$updated) {
                    throw new \Exception('Failed to update service');
                }

                DB::commit();

                $service = $service->fresh();

                Log::info('Service updated successfully', [
                    'service_id' => $service->id,
                    'updated_data' => $service->toArray()
                ]);

                return response()->json([
                    'message' => 'Service updated successfully',
                    'service' => new ServiceResource($service)
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to update service', [
                    'service_id' => $id,
                    'error' => $e->getMessage()
                ]);
                return response()->json(['message' => 'Failed to update service', 'error' => $e->getMessage()], 500);
            }
        } catch (\Exception $e) {
            Log::error('Service not found or other error', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Service not found'], 404);
        }
    }

    public function destroy($id)
    {
        try {
            // First find the service
            $service = Service::findOrFail($id);

            Log::info('Starting service deletion', [
                'service_id' => $id,
                'service_data' => $service->toArray()
            ]);

            DB::beginTransaction();
            try {
                $deleted = $service->delete();

                if (!$deleted) {
                    throw new \Exception('Failed to delete service');
                }

                DB::commit();

                Log::info('Service deleted successfully', [
                    'service_id' => $id
                ]);

                return response()->json(['message' => 'Service deleted successfully'], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to delete service', [
                    'service_id' => $id,
                    'error' => $e->getMessage()
                ]);
                return response()->json(['message' => 'Failed to delete service', 'error' => $e->getMessage()], 500);
            }
        } catch (\Exception $e) {
            Log::error('Service not found or other error', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Service not found'], 404);
        }
    }
}

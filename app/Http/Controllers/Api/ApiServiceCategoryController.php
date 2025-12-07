<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceCategory;
use App\Http\Resources\ServiceCategoryResource;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Log;


class ApiServiceCategoryController extends Controller
{

    public function index(Request $request)
    {
        $limit = $request->query('limit', null);

        $query = ServiceCategory::latest();

        if ($limit && is_numeric($limit)) {
            $query->limit($limit);
        }

        $serviceCategories = $query->get();

        if ($serviceCategories->count() > 0) {
            return ServiceCategoryResource::collection($serviceCategories);
        } else {
            return response()->json(['count' => $serviceCategories->count(),'message' => 'No service categories found'], 200);
        }
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $serviceCategory = ServiceCategory::create([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Service category created successfully'], 201);
    }


    public function show(ServiceCategory $serviceCategory)
    {
        return new ServiceCategoryResource($serviceCategory);
    }

    public function update(Request $request, ServiceCategory $serviceCategory)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $serviceCategory->update([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Service category updated successfully'], 201);
    }


    public function destroy(ServiceCategory $serviceCategory)
    {
        $serviceCategory->delete();
        return response()->json(['message' => 'Service category deleted successfully'], 200);
    }
}

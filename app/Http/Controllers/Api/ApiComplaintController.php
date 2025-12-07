<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Complaint;
use App\Http\Resources\ComplaintResource;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use App\Models\Photo;
use Illuminate\Support\Facades\Log;

class ApiComplaintController extends Controller
{

    public function index(Request $request)
    {
        $limit = $request->query('limit', null);
        $status = $request->query('status', null);

        $query = Complaint::with('photos')->latest();

        if ($status) {
            $query->where('status', $status);
        }

        if ($limit && is_numeric($limit)) {
            $query->limit($limit);
        }

        $complaints = $query->get();

        if ($complaints->isEmpty()) {
            return response()->json(['message' => 'No complaints found'], 404);
        }

        return response()->json([
            'count' => $complaints->count(),
            'data' => ComplaintResource::collection($complaints),
        ]);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:255',
            'description' => 'required|string',
            'status' => 'sometimes|string|in:unresolved,resolved,in progress',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed:', ['errors' => $validator->errors()]);
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            $complaint = Complaint::create([
                'name' => $request->input('name'),
                'number' => $request->input('number'),
                'description' => $request->input('description'),
                'status' => $request->input('status', 'unresolved'),
            ]);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $image) {
                    Log::info('Photo found in request');
                    $image_name = time() . '_' . preg_replace('/\s+/', '_', $image->getClientOriginalName());
                    $image->move(public_path('images'), $image_name);

                    $photo = new Photo([
                        'photoable_type' => Complaint::class,
                        'photoable_id' => $complaint->id,
                        'photo_url' => asset('images/' . urlencode($image_name))
                    ]);
                    $complaint->photos()->save($photo);
                }
            }

            $complaint->load('photos');


            return response()->json([
                'message' => 'Complaint created successfully',
                'complaint' => new ComplaintResource($complaint)
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating complaint', 'error' => $e->getMessage()], 500);
        }
    }
    public function show(Complaint $complaint)
    {
        return new ComplaintResource($complaint->load('photos'));
    }

    public function update(Request $request, Complaint $complaint)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|nullable|string|max:255',
            'number' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|required|string',
            'status' => 'sometimes|string|in:unresolved,resolved,in progress',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $complaint->update([
            'name' => $request->input('name', $complaint->name),
            'number' => $request->input('number', $complaint->number),
            'description' => $request->input('description', $complaint->description),
            'status' => $request->input('status', $complaint->status),
        ]);

        if ($request->hasFile('photos')) {
            // Remove existing photos
            $complaint->photos()->delete();

            foreach ($request->file('photos') as $image) {
                $image_name = time() . '_' . preg_replace('/\s+/', '_', $image->getClientOriginalName());
                $image->move(public_path('images'), $image_name);

                $photo = new Photo([
                    'photoable_type' => Complaint::class,
                    'photoable_id' => $complaint->id,
                    'photo_url' => asset('images/' . urlencode($image_name))
                ]);
                $complaint->photos()->save($photo);
            }
        } else {
            $complaint->photos()->delete();
        }

        return response()->json(['message' => 'Complaint updated successfully', 'complaint' => new ComplaintResource($complaint->fresh()->load('photos'))], 200);
    }
    public function destroy(Complaint $complaint)
    {
        $complaint->delete();
        return response()->json(['message' => 'Complaint deleted successfully'], 200);
    }

    public function trashed()
    {
        $trashedComplaints = Complaint::onlyTrashed()->with('photos')->get();
        return ComplaintResource::collection($trashedComplaints);
    }

    public function restore($id)
    {
        $complaint = Complaint::withTrashed()->findOrFail($id);
        $complaint->restore();
        return response()->json(['message' => 'Complaint restored successfully'], 200);
    }

    public function forceDelete($id)
    {
        $complaint = Complaint::withTrashed()->findOrFail($id);
        $complaint->forceDelete();
        return response()->json(['message' => 'Complaint permanently deleted'], 200);
    }
}

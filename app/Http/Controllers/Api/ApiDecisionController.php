<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Decision;
use App\Http\Resources\DecisionResource;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use App\Models\Photo;

class ApiDecisionController extends Controller
{

    public function index(Request $request)
    {
        $limit = $request->query('limit', null);

        $query = Decision::with('photos')->latest();

        if ($limit && is_numeric($limit)) {
            $query->limit($limit);
        }

        $decisions = $query->get();

        if ($decisions->isEmpty()) {
            return response()->json(['message' => 'No decisions found'], 404);
        }

        return response()->json([
            'count' => $decisions->count(),
            'data' => DecisionResource::collection($decisions),
        ]);


    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'decision_id' => 'required|integer',
            'decision_date' => 'required|date',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            $decision = Decision::create([
                'decision_id' => (int) $request->decision_id,
                'decision_date' => $request->decision_date,
                'title' => $request->title,
                'description' => $request->description,
            ]);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $image) {
                    $image_name = time() . '_' . preg_replace('/\s+/', '_', $image->getClientOriginalName());
                    $image->move(public_path('images'), $image_name);

                    $photo = new Photo([
                        'photoable_type' => Decision::class,
                        'photoable_id' => $decision->id,
                        'photo_url' => asset('images/' . urlencode($image_name))
                    ]);
                    $decision->photos()->save($photo);
                }
            }

            $decision->load('photos');

            return response()->json(['message' => 'Decision created successfully', 'decision' => new DecisionResource($decision)], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating decision', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Decision $decision)
    {
        return new DecisionResource($decision->load('photos'));
    }

    public function update(Request $request, Decision $decision)
    {
        $validator = Validator::make($request->all(), [
            'decision_id' => 'required|integer',
            'decision_date' => 'required|date',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $decision->update([
            'decision_id' => $request->decision_id,
            'decision_date' => $request->decision_date,
            'title' => $request->title,
            'description' => $request->description,
        ]);

        if ($request->hasFile('photos')) {
            // Remove existing photos
            $decision->photos()->delete();

            foreach ($request->file('photos') as $image) {
                $image_name = time() . '_' . preg_replace('/\s+/', '_', $image->getClientOriginalName());
                $image->move(public_path('images'), $image_name);

                $photo = new Photo([
                    'photoable_type' => Decision::class,
                    'photoable_id' => $decision->id,
                    'photo_url' => asset('images/' . urlencode($image_name))
                ]);
                $decision->photos()->save($photo);
            }
        }

        return response()->json(['message' => 'Decision updated successfully', 'decision' => new DecisionResource($decision->fresh()->load('photos'))], 200);
    }


    public function destroy(Decision $decision)
    {
        $decision->delete();
        return response()->json(['message' => 'Decision deleted successfully'], 200);
    }
}

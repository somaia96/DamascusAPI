<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use App\Http\Resources\ActivityResource;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use App\Models\Photo;
use Illuminate\Support\Facades\Log;
use App\Models\ActivityType;
use App\Exceptions\UnauthorizedException;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


class ApiActivityController extends Controller
{

    public function index(Request $request)
    {
        $limit = $request->query('limit', null);
        $activityTypeName = $request->query('activity_type_name', null);

        $query = Activity::with(['photos', 'activityType'])->latest();

        if ($activityTypeName) {
            $query->whereHas('activityType', function ($q) use ($activityTypeName) {
                $q->where('name', $activityTypeName);
            });
        }

        if ($limit && is_numeric($limit)) {
            $query->limit($limit);
        }

        $activities = $query->get();

        if ($activities->isEmpty()) {
            return response()->json(['message' => 'No activities found'], 404);
        }

        return response()->json([
            'count' => $activities->count(),
            'data' => ActivityResource::collection($activities),
        ]);
    }

    public function store(Request $request)
    {
        $admin = Admin::where('email', $request->email)->first();

        if (! $admin || ! Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Validation rules
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'activity_type_name' => 'required|string|exists:activity_types,name',
            'activity_date' => 'required|date',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed:', ['errors' => $validator->errors()]);
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            $activityType = ActivityType::where('name', $request->input('activity_type_name'))->firstOrFail();

            $activity = Activity::create([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'activity_type_id' => $activityType->id,
                'activity_date' => $request->input('activity_date'),
            ]);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $image) {
                    $image_name = time() . '_' . preg_replace('/\s+/', '_', $image->getClientOriginalName()); // Replace spaces with underscores
                    $image->move(public_path('images'), $image_name);

                    $photo = new Photo([
                        'photoable_type' => Activity::class,
                        'photoable_id' => $activity->id,
                        'photo_url' => asset('images/' . urlencode($image_name))
                    ]);
                    $activity->photos()->save($photo);
                }
            }

            $activity->load('photos');

            return response()->json(['message' => 'Activity created successfully', 'activity' => new ActivityResource($activity)], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating activity', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Activity $activity)
    {
        return new ActivityResource($activity->load('photos'));
    }

    public function update(Request $request, Activity $activity)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'activity_type_name' => 'sometimes|required|string|exists:activity_types,name',
            'activity_date' => 'sometimes|required|date',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        if ($request->has('activity_type_name')) {
            $activityType = ActivityType::where('name', $request->input('activity_type_name'))->firstOrFail();
            $activity->activity_type_id = $activityType->id;
        }

        $activity->update([
            'title' => $request->input('title', $activity->title),
            'description' => $request->input('description', $activity->description),
            'activity_date' => $request->input('activity_date', $activity->activity_date),
        ]);

        if ($request->hasFile('photos')) {
            // Remove existing photos
            $activity->photos()->delete();

            foreach ($request->file('photos') as $image) {
                $image_name = time() . '_' . preg_replace('/\s+/', '_', $image->getClientOriginalName()); // Replace spaces with underscores
                $image->move(public_path('images'), $image_name);

                $photo = new Photo([
                    'photoable_type' => Activity::class,
                    'photoable_id' => $activity->id,
                    'photo_url' => asset('images/' . urlencode($image_name))
                ]);
                $activity->photos()->save($photo);
            }
        }

        return response()->json(['message' => 'Activity updated successfully', 'activity' => new ActivityResource($activity->fresh()->load('photos'))], 200);
    }

    public function destroy(Activity $activity)
    {
        $activity->delete();
        return response()->json(['message' => 'Activity deleted successfully'], 200);
    }
}

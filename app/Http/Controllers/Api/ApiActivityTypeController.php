<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityType;
use App\Http\Resources\ActivityTypeResource;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;


class ApiActivityTypeController extends Controller
{

    public function index(Request $request)
    {
        $limit = $request->query('limit', null);

        $query = ActivityType::latest();

        if ($limit && is_numeric($limit)) {
            $query->limit($limit);
        }

        $activityTypes = $query->get();

        if ($activityTypes->isEmpty()) {
            return response()->json(['message' => 'No activity types found'], 404);
        }

        return response()->json([
            'count' => $activityTypes->count(),
            'data' => ActivityTypeResource::collection($activityTypes),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $activityType = ActivityType::create([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Activity type created successfully'], 201);
    }

    public function show(ActivityType $activityType)
    {
        return new ActivityTypeResource($activityType);
    }


    public function update(Request $request, ActivityType $activityType)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $activityType->update([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Activity type updated successfully'], 201);
    }


    public function destroy(ActivityType $activityType)
    {
        $activityType->delete();
        return response()->json(['message' => 'Activity type deleted successfully'], 200);
    }
}


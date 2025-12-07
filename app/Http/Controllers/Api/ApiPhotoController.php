<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Photo;
use App\Http\Resources\PhotoResource;
use Illuminate\Support\Facades\Validator;

class ApiPhotoController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->query('limit', null);
        $query = Photo::latest();
        if ($limit && is_numeric($limit)) {
            $query->limit($limit);
        }
        $photos = $query->get();
        return $photos->count() > 0 ? PhotoResource::collection($photos) : response()->json(['count' => $photos->count(),'message' => 'No photos found'], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'photoable_id' => 'required|string|max:255',
            'photoable_type' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $photo = Photo::create([
            'photoable_id' => $request->photoable_id,
            'photoable_type' => $request->photoable_type,
        ]);

        if ($request->hasFile('photo')) {
            $photo->addMediaFromRequest('photo')->toMediaCollection('photos');
        }

        return response()->json(['message' => 'Photo uploaded successfully', 'photo' => new PhotoResource($photo)], 201);
    }

    public function show(Photo $photo)
    {
        return new PhotoResource($photo);
    }

    public function update(Request $request, Photo $photo)
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'photoable_id' => 'nullable|string|max:255',
            'photoable_type' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        if ($request->has('photoable_id') && $request->has('photoable_type')) {
            $photo->update([
                'photoable_id' => $request->photoable_id,
                'photoable_type' => $request->photoable_type,
            ]);
        }

        if ($request->hasFile('photo')) {
            $photo->clearMediaCollection('photos');
            $photo->addMediaFromRequest('photo')->toMediaCollection('photos');
        }

        return response()->json(['message' => 'Photo updated successfully', 'photo' => new PhotoResource($photo)], 200);
    }

    public function destroy(Photo $photo)
    {
        $photo->delete();
        return response()->json(['message' => 'Photo deleted successfully'], 200);
    }
}

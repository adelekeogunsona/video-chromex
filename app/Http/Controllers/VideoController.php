<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVideoRequest;
use App\Http\Resources\VideoResource;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class VideoController extends Controller
{
    /**
     * Display all videos.
     */
    public function index()
    {
        $videos = Video::all();

        if ($videos->isEmpty()) {
            return response()->json([
                'message' => 'No videos found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status' => 'success',
            'data' => VideoResource::collection($videos),
        ], Response::HTTP_OK);
    }

    /**
     * Store a new video.
     */
    public function store(StoreVideoRequest $request)
    {
        // check if name already exists as title in videos table
        $video = Video::where('title', $request->name)->first();

        if ($video) {

            return response()->json([
                'status' => 'success',
                'message' => 'Video appended successfully.',
                'data' => new VideoResource($video),
            ], Response::HTTP_OK);
        } else {
            $path = Storage::putFile('public', $request->file('video'));

            $video = Video::create([
                'title' => $request->name,
                'path' => $path,
                'public_url' => env('APP_URL') . Storage::url($path),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Video uploaded successfully.',
                'data' => new VideoResource($video),
            ], Response::HTTP_OK);
        }
    }

    /**
     * Display the specified video.
     */
    public function show(Video $video)
    {
        // check if video exists
        if (!Video::where('id', $video->id)->exists()) {
            return response()->json([
                'message' => 'Video not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status' => 'success',
            'data' => new VideoResource($video),
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified video from storage.
     */
    public function destroy(Video $video)
    {
        // check if video exists
        if (!Video::where('id', $video->id)->exists()) {
            return response()->json([
                'message' => 'Video not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        Storage::delete($video->path);

        $video->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Video deleted successfully.',
        ], Response::HTTP_OK);
    }
}
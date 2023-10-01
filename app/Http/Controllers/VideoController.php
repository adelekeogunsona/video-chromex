<?php

namespace App\Http\Controllers;

use \FFMpeg\Format\Video\WebM;
use App\Http\Requests\StoreVideoRequest;
use App\Http\Resources\VideoResource;
use App\Models\Video;
use FFMpeg\FFMpeg;
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
            'message' => 'Videos retrieved successfully.',
            'data' => VideoResource::collection($videos),
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified video.
     */
    public function show($id)
    {
        // check if video exists
        if (!Video::where('id', $id)->exists()) {
            return response()->json([
                'message' => 'Video not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Video retrieved successfully.',
            'data' => new VideoResource(Video::where('id', $id)->first()),
        ], Response::HTTP_OK);
    }

    /**
     * Start streaming a new video.
     */
    public function stream(StoreVideoRequest $request)
    {
        $validated = (object) $request->validated();

        $chunk_content = file_get_contents($validated->blob);
        Storage::append('public/temp/' . $validated->title . '/video.bin', $chunk_content);

        // check if video doesn't exist
        if (!Video::where('title', $validated->title)->exists()) {
            // create a new video record
            $video = Video::create([
                'title' => $validated->title,
                'path' => null,
                'public_url' => null,
            ]);
        }

        return response()->json([
            'message' => 'Video streamed successfully.',
        ], Response::HTTP_OK);
    }

    /**
     * Mark video as completed.
     */
    public function stop($id)
    {
        // check if video exists
        if (!Video::where('id', $id)->exists()) {
            return response()->json([
                'message' => 'Video not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $video = Video::where('id', $id)->first();

        // check if video is already completed
        if ($video->path !== null) {
            return response()->json([
                'message' => 'Video already completed.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // convert the .bin file to .webm
        $video_path = 'public/temp/' . $video->title . '/video.bin';
        $video_webm_path = 'public/videos/' . $video->title . '/video.webm';
        $video_webm_url = Storage::url($video_webm_path);

        // load ffmpeg binaries path
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => 'C:/FFmpeg/bin/ffmpeg.exe',   // changing this later
            'ffprobe.binaries' => 'C:/FFmpeg/bin/ffprobe.exe'
        ]);

        $video = $ffmpeg->open(Storage::path($video_path));
        $video->save(new WebM(), Storage::path($video_webm_path));

        // update the video record
        Video::where('id', $id)->update([
            'path' => $video_webm_path,
            'public_url' => $video_webm_url,
        ]);
    }

    /**
     * Remove the specified video from storage.
     */
    // public function destroy($id)
    // {
    //     $video = Video::where('id', $id)->first();

    //     // check if video exists
    //     if (!Video::where('id', $video->id)->exists()) {
    //         return response()->json([
    //             'message' => 'Video not found.',
    //         ], Response::HTTP_NOT_FOUND);
    //     }

    //     Storage::delete($video->path);

    //     $video->delete();

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Video deleted successfully.',
    //     ], Response::HTTP_OK);
    // }
}
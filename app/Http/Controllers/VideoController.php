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
     * Retrieve all videos.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/videos",
     *     summary="Retrieve all videos",
     *     operationId="getVideos",
     *     tags={"Endpoints"},
     *     @OA\Response(
     *         response=200,
     *         description="Videos retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Videos retrieved successfully."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                     ),
     *                     @OA\Property(
     *                         property="title",
     *                         type="string",
     *                         example="Video-1"
     *                     ),
     *                     @OA\Property(
     *                         property="public_url",
     *                         type="string",
     *                         example="http://example.com/storage/videos/87uiytbHGf-1/video.webm"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No videos found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="No videos found."
     *             )
     *         )
     *     )
     * )
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
     * Retrieve a video.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/video/{id}",
     *     summary="Retrieve a video",
     *     operationId="getVideo",
     *     tags={"Endpoints"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Video ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example="1"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Video retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Video retrieved successfully."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer",
     *                     example="1"
     *                 ),
     *                 @OA\Property(
     *                     property="title",
     *                     type="string",
     *                     example="Video-1"
     *                 ),
     *                 @OA\Property(
     *                     property="public_url",
     *                     type="string",
     *                     example="http://example.com/storage/videos/87uiytbHGf-1/video.webm"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Video not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Video not found."
     *             )
     *         )
     *     )
     * )
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
     * Start streaming a video.
     *
     * @param \App\Http\Requests\StoreVideoRequest $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path="/video/stream",
     *     summary="Stream a video",
     *     operationId="streamVideo",
     *     tags={"Endpoints"},
     *     @OA\RequestBody(
     *         description="Video details",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="title",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="blob",
     *                     type="file",
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Video streamed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Video streamed successfully."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Video already completed",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Video already completed."
     *             )
     *         )
     *     )
     * )
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
            'message' => 'Video streamed successfully.'
        ], Response::HTTP_OK);
    }

    /**
     * Stop streaming a video.
     *
     * @param int $title
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/video/end-stream/{title}",
     *     summary="Stop streaming a video",
     *     operationId="stopVideo",
     *     tags={"Endpoints"},
     *     @OA\Parameter(
     *         name="title",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             example="Video-1"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Video completed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Video completed successfully."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer",
     *                     example="1"
     *                 ),
     *                 @OA\Property(
     *                     property="title",
     *                     type="string",
     *                     example="Video-1"
     *                 ),
     *                 @OA\Property(
     *                     property="url",
     *                     type="string",
     *                     example="http://example.com/storage/videos/87uiytbHGf.webm"
     *                 ),
     *                @OA\Property(
     *                    property="transcript",
     *                   type="string",
     *                  example="This is the transcript of the video."
     *                )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Video already completed",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Video already completed."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Video not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Video not found."
     *             )
     *         )
     *     )
     * )
     */
    public function stop($title)
    {
        // check if video exists
        if (!Video::where('title', $title)->exists()) {
            return response()->json([
                'message' => 'Video not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $video = Video::where('title', $title)->first();

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
            'ffmpeg.binaries' => 'C:/FFmpeg/bin/ffmpeg.exe',
            // changing this later
            'ffprobe.binaries' => 'C:/FFmpeg/bin/ffprobe.exe'
        ]);

        $video = $ffmpeg->open(Storage::path($video_path));
        $video->save(new WebM(), Storage::path($video_webm_path));

        // update the video record
        Video::where('title', $title)->update([
            'path' => $video_webm_path,
            'public_url' => $video_webm_url,
        ]);

        return response()->json([
            'message' => 'Video completed successfully.',
            'data' => new VideoResource($video),
        ], Response::HTTP_OK);
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
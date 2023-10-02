<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * ),
 * @OA\Info(
 *     title="API DOCUMENTATION",
 *     version="0.1",
 *     description="This is the API documentation for the Video Streaming Chrome Extension.",
 * ),
 * @OA\PathItem(path="/"),
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}

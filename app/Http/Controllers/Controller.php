<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
/*
 * @OA\Info(
 *    title="User Authentication API",
 *    version="1.0.0"
 * )
 * @OA\PathItem(
 *   path="/api/auth",
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}

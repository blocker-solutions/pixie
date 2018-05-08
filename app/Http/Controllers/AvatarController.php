<?php

namespace Pixie\Http\Controllers;

use Pixie\Jobs\DownloadImage;
use Illuminate\Http\Response;
use Laravel\Lumen\Routing\Controller;

/**
 * Class AvatarController.
 *
 * Handles the HTTP interface for Pixie avatar service.
 */
class AvatarController extends Controller
{
    /**
     * Handles the default avatar display GET method.
     *
     * @param string $username
     *
     * @return Response
     */
    public function show(string $username)
    {
        // find the avatar for the given username.
        $image = $this->dispatchNow(new DownloadImage($username));

        // creates and return a response with HTTP status 200, and the content aas being WEBP.
        return response($image, 200, ['Content-Type' => 'image/webp']);
    }
}

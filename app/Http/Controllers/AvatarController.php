<?php

namespace Pixie\Http\Controllers;

use Illuminate\Http\Response;
use Laravel\Lumen\Routing\Controller;
use Pixie\Jobs\DownloadImage;

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
     * @param string $extension
     *
     * @return Response
     */
    public function show(string $username, string $extension = 'jpeg')
    {
        // if the extension is not jpeg or webp, default to jpeg
        if ($extension != 'jpeg' || $extension != 'webp') {
            $extension = 'jpeg';
        }

        // find the avatar for the given username.
        $image = $this->dispatchNow(new DownloadImage($username, $extension));

        // creates and return a response with HTTP status 200, and the content aas being WEBP.
        return response($image, 200, [
            'Content-Type'  => "image/{$extension}",
            'Cache-Control' => [ 'public', 'max-age=86400' ],
        ]);
    }
}

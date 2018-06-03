<?php

namespace Pixie\Http\Controllers;

use Illuminate\Cache\CacheManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Lumen\Routing\Controller;
use Pixie\Jobs\DownloadImage;
use Pixie\Services\IPFS\Client as IPFS;

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
    public function show(string $username, string $extension = 'png')
    {
        // check the requested (or default) extension.
        $extension = $this->checkExtension($extension);

        // try to parse a cache (eTag) IPFS hash from request.
        $hash = $this->tryCachedIpfsTag();

        // redirect to the IPFS if the browser provided a valid eTag hash.
        if ($hash) {
            return $this->redirectToIPFS($hash, $extension);
        }

        // find the avatar for the given username.
        $image = $this->dispatchNow(new DownloadImage($username, $extension));

        // return error when no image detected.
        if (!$image) {
            return response()->json('error');
        }

        // add the image on IPFS and get the hash.
        $hash = $this->pushToIPFS($image);

        // build the IPFS url for the response.
        $ipfsUrl = "https://gateway.ipfs.io/ipfs/{$hash}";

        // creates and return a response with HTTP status 200.
        // this response is direct, but sets the eTag and
        // pre-fetch directly from IPFS.
        return response($image, 200, [
            'Content-Type'     => "image/{$extension}",
            'Cache-Control'    => ['public', 'max-age=60'],
            'Etag'             => 'W/"'.$hash.'"',
            'Link'             => "<$ipfsUrl>; rel=preload; as=image; crossorigin",
            'Content-Location' => $ipfsUrl,
        ]);
    }

    /**
     * Check the requested extension.
     *
     * @param string $extension
     *
     * @return string
     */
    protected function checkExtension($extension = 'png') : string
    {
        // if the extension is not jpeg or webp, default to jpeg
        if ($extension != 'jpeg' && $extension != 'webp' && $extension != 'png') {
            return 'png';
        }

        // return the original extension case matched.
        return $extension;
    }

    /**
     * Detect eTag IPFS hash.
     *
     * @return null|string
     */
    protected function tryCachedIpfsTag() : ?string
    {
        /** @var Request $request */
        $request = app()->make('request');

        // parse request eTag.
        $eTag = array_first((array) $request->getETags(), null);

        // return when no e-tag was detected.
        if (!$eTag) {
            return null;
        }

        // extract the actual IPFS hash from eTag.
        $clean = mb_substr($eTag, 3, 46, 'UTF-8');

        // return null when not present.
        if (!$clean || mb_strlen($clean) < 20) {
            return null;
        }

        // make cache instance.
        /** @var CacheManager $cache */
        $cache = app()->make('cache');

        // return the hash status (if created by pixie).
        return $cache->has($clean) ? $clean : null;
    }

    /**
     * Publish to IPFS.
     *
     * @param string $image Image contents (string)
     *
     * @return null|string Hash, if saved correctly.
     */
    protected function pushToIPFS($image)
    {
        // create an IPFS instance.
        /** @var IPFS $ipfs */
        $ipfs = app()->make('ipfs');

        // add on IPFS and get the hash.
        $hash = $ipfs->add($image);

        // return null if IPFS did not returned a hash.
        if (!$hash) {
            return null;
        }

        // make cache instance.
        /** @var CacheManager $cache */
        $cache = app()->make('cache');

        // remember the hash key as built by pixie.
        $cache->remember($hash, 60, function () {
            return true;
        });

        // return the actual hash.
        return $hash;
    }

    /**
     * Redirect to a previously built IPFS image.
     *
     * @param string $hash
     * @param string $extension
     *
     * @return Response
     */
    protected function redirectToIPFS($hash, $extension = 'png') : Response
    {
        $ipfsUrl = "https://gateway.ipfs.io/ipfs/{$hash}";

        return response('', 304, [
            'Content-Type'     => "image/{$extension}",
            'Cache-Control'    => ['public', 'max-age=60'],
            'Etag'             => 'W/"'.$hash.'"',
            'Link'             => "<$ipfsUrl>; rel=preload; as=image; crossorigin",
            'Content-Location' => $ipfsUrl,
        ]);
    }
}

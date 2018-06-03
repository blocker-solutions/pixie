<?php

namespace Pixie\Jobs;

use Intervention\Image\Constraint;
use Unirest\Request;

/**
 * Class DownloadImage.
 *
 * Image downloader job.
 */
class DownloadImage extends Job
{
    /**
     * @var array Headers to use when requesting the images.
     */
    protected $downloadHeaders = [
        'Accept' => 'image/jpg, image/jpeg, image/webp, image/png, image/svg',
    ];

    /**
     * Actually download the images.
     *
     * @param string $avatarURL
     *
     * @return \Intervention\Image\Image|null
     */
    protected function downloadImage(string $avatarURL)
    {
        // try
        try {
            // do the HTTP request to retrieve the image.
            $response = Request::get($avatarURL, $this->downloadHeaders);

            // catch
        } catch (\Exception $e) {
            // case
            return null;
        }

        // factory an image out of it.
        return $this->imageManager->make($response->raw_body);
    }

    /**
     * Find the image, from URL or auto generated/.
     *
     * @return string
     */
    protected function findImage()
    {
        // find the avatar URL.
        $avatarURL = $this->dispatchNow(new GetAvatarURL($this->username));

        // start source as null.
        $source = null;

        // call the download method if a image URL was found.
        if ($avatarURL) {
            try {
                $source = $this->downloadImage($avatarURL);
            } catch (\Exception $e) {
                $source = null;
            }
        }

        // if the was no URl or the download fails...
        if (!$source) {
            // generate a random initials avatar.
            $source = $this->dispatchNow(new RandomAvatar($this->username));
        }

        // create a resize constraint function.
        $resizeConstraint = function (Constraint $constraint) {
            $constraint->upsize();
        };

        // resize the image into a squared, non stretched image.
        $source->fit($this->baseSize, $this->baseSize, $resizeConstraint);

        // encode the result as webp and return.
        return $source->encode('png', 80)->getEncoded();
    }

    /**
     * Execute the job.
     *
     * @return null|string
     */
    public function handle()
    {
        // define the image finder function.
        $imageFinder = function () {
            // call the class find image logic.
            return $this->findImage();
        };

        // find, resize, cache and return the avatar.
        $image = $this->remember($this->cacheKey, $this->ttl, $imageFinder);

        // custom format encoder function.
        $formatEncoder = function () use ($image) {
            // encode on the custom format.
            return $this->imageManager->make($image)
                ->encode($this->extension, 80)
                ->getEncoded();
        };

        // if the extension is not the default format (jpeg).
        if ($this->extension != 'png') {
            // convert and cache the format.
            return $this->remember("{$this->cacheKey}.{$this->extension}", $this->ttl, $formatEncoder);
        }

        // return the original image otherwise.
        return $image;
    }
}

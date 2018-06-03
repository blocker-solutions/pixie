<?php

namespace Pixie\Providers;

use Illuminate\Support\ServiceProvider;
use Intervention\Image\ImageManager;

/**
 * Class ImageServiceProvider.
 *
 * Custom intervention image loader for lumen (config bypass).
 */
class ImageServiceProvider extends ServiceProvider
{
    /**
     * Register resources.
     */
    public function register()
    {
        // register an ImageManager instance on the IoC.
        $this->app->singleton('image', function () {
            return new ImageManager(['driver' => 'imagick']);
        });

        // alis the manager as Image.
        $this->app->alias('image', ImageManager::class);
    }
}

<?php

namespace Pixie\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Intervention\Image\ImageManager;
use Laravel\Lumen\Routing\ProvidesConvenienceMethods;
use LightRPC\Client;

/**
 * Class Job.
 *
 * Base Job implementation.
 */
abstract class Job implements ShouldQueue
{
    // make jobs queueable.
    use InteractsWithQueue, Queueable, SerializesModels;
    // enable dispatching capabilities.
    use ProvidesConvenienceMethods;

    /**
     * @var Client LightRPC client instance.
     */
    protected $steem;

    /**
     * @var string Username of the account which owns the avatar.
     */
    protected $username;

    /**
     * @var ImageManager instance.
     */
    protected $imageManager;

    /**
     * @var CacheManager instance.
     */
    protected $cacheManager;

    /**
     * @var string Cache key to store the image under.
     */
    protected $cacheKey;

    /**
     * @var int Base storage image size.
     */
    protected $baseSize = 128;

    /**
     * @var int Time-to-live for the cached avatar.
     */
    protected $ttl = 1440;

    /**
     * @var string Custom font face to be used when generating initial avatars.
     */
    protected $customFont = 'app/fonts/Oswald-Regular.ttf';

    /**
     * @var string Default extension / encoding.
     */
    protected $extension = 'jpeg';

    /**
     * Job constructor.
     *
     * @param string $username
     * @param string $extension
     */
    public function __construct(string $username, string $extension = 'jpeg')
    {
        // light RPC instance.
        $this->steem = new Client(env('STEEM_API', 'https://api.steemit.com'));

        // assign the username on the job instance.
        $this->username = $username;

        // determine the cache key.
        $this->cacheKey = "avatar.{$this->baseSize}.{$username}";

        // assign the manager instance.
        $this->imageManager = app()->make('image');

        // assign a cache manager instance.
        $this->cacheManager = app()->make('cache');

        // default encoding extension.
        $this->extension = $extension;
    }

    /**
     * Cache remember wrapper.
     *
     * @param string   $key
     * @param int      $ttl
     * @param \Closure $what
     *
     * @return mixed
     */
    protected function remember(string $key, int $ttl, \Closure $what)
    {
        return $this->cacheManager->remember($key, $ttl, $what);
    }
}

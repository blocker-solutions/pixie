<?php

namespace Pixie\Providers;

use Illuminate\Support\ServiceProvider;
use LightRPC\Client as LightRPC;

/**
 * Class SteemServiceProvider.
 *
 * Singleton the Steem LightRPC client on the application container.
 */
class SteemServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('steem', function () {
            // get the current steem api from env.
            $apiURL = env('STEEM_API', 'https://api.steemit.com');
            // return a new instance of the LightRPC client.
            return new LightRPC($apiURL);
        });
    }
}

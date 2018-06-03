<?php

namespace Pixie\Providers;

use Illuminate\Support\ServiceProvider;
use Pixie\Services\IPFS\Client as IPFS;

/**
 * Class IPFSServiceProvider.
 *
 * Provider for IPFS resources.
 */
class IPFSServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('ipfs', function () {
            // get the current steem api from env.
            $apiURL = env('IPFS_API', 'http://localhost:5001/api/v0/');
            // return a new instance of the LightRPC client.
            return new IPFS($apiURL);
        });
    }
}

<?php

namespace Biigle\RoundRobinQueue;

use Illuminate\Support\ServiceProvider;

class RoundRobinQueueServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['queue']->addConnector('roundrobin', function() {
            return new RoundRobinConnector;
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

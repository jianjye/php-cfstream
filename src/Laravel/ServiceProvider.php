<?php

namespace JJSee\CFStream\Laravel;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->publishes([
        	__DIR__.'/config/cloudflare.php' => config_path('cloudflare.php'),
        ]);
    }
}

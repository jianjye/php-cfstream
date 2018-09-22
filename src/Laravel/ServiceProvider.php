<?php

namespace JJSee\CFStream\Laravel;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->publishes([
        	__DIR__.'/config/cfstream.php' => config_path('cfstream.php'),
        ]);
    }
}

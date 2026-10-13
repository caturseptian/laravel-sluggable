<?php

namespace Caturseptian\Sluggable;

use Caturseptian\Sluggable\Console\EjectCommand;
use Illuminate\Support\ServiceProvider;

class SluggableServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([EjectCommand::class]);
        }
    }
}

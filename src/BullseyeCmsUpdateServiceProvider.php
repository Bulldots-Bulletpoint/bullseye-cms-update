<?php

namespace BulldotsBulletpoint\BullseyeCmsUpdate;

use BulldotsBulletpoint\BullseyeCmsUpdate\Commands\UpdateCMS;
use Illuminate\Support\ServiceProvider;

class BullseyeCmsUpdateServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->commands(
            [
                UpdateCMS::class,
            ]
        );
    }

    /**
     * Register the application services.
     */
    public function register()
    {

    }
}

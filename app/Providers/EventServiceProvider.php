<?php

namespace App\Providers;

use App\Events\UpdatedLocationTukang;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UpdatedLocationTukang::class => [
            // Add any listeners here if needed
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
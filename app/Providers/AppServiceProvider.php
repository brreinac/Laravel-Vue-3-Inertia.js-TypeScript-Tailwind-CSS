<?php

namespace App\Providers;

use App\Events\TaskStatusChanged;
use App\Listeners\LogTaskStatusChange;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Services are intentionally kept close to their use cases.
    }

    public function boot(): void
    {
        Event::listen(TaskStatusChanged::class, LogTaskStatusChange::class);
    }
}

<?php

namespace App\Providers;

use App\Events\TaskStatusChanged;
use App\Listeners\LogTaskStatusChange;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
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

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by(
                    strtolower((string) $request->input('email'))
                    . '|'
                    . $request->ip()
                );
        });
    }
}
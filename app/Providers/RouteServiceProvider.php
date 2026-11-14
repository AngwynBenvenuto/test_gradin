<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    protected $namespace = 'App\\Http\\Controllers'; // Add this line or uncomment it
    
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Tippler dashboard polls /status every second and probes connectivity
        // periodically, which alone saturates the default 60/min 'api' limiter
        // (and gets multiplied by every open kiosk tab). Give it its own,
        // more generous budget so normal polling never trips a 429.
        RateLimiter::for('tippler', function (Request $request) {
            return Limit::perMinute(600)->by($request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->namespace($this->namespace)
                ->prefix('api')
                ->group(base_path('routes/api.php'));;

            Route::middleware('web')
                ->namespace($this->namespace) // Add this ->namespace() call
                ->group(base_path('routes/web.php'));
        });
    }
}

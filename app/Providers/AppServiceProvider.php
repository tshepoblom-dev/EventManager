<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    /**
     * Fix #21: AppServiceProvider was completely empty.
     * Added model optimizations, strict mode (dev), and Tailwind paginator.
     */
    public function boot(): void
    {
        // Prevent lazy loading N+1 queries in local/testing environments.
        // In production this is left off so a forgotten ->load() doesn't 500.
        Model::preventLazyLoading(! app()->isProduction());

        // Prevent silently discarding mass-assigned attributes that aren't $fillable
        Model::preventSilentlyDiscardingAttributes(! app()->isProduction());

        // Use Tailwind CSS pagination views (matches the rest of the UI)
        Paginator::useTailwind();

        // Log slow queries (>100 ms) in local/staging for easy identification
        if (! app()->isProduction()) {
            DB::listen(function ($query) {
                if ($query->time > 100) {
                    logger()->warning('Slow query detected', [
                        'sql'      => $query->sql,
                        'bindings' => $query->bindings,
                        'time_ms'  => $query->time,
                    ]);
                }
            });
        }
    }
}

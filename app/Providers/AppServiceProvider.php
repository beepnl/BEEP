<?php

namespace App\Providers;

use App\ChecklistFactory;
use App\HiveFactory;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * Paginate a standard Laravel Collection.
         *
         * @param  int  $perPage
         * @param  int  $total
         * @param  int  $page
         * @param  string  $pageName
         * @return array
         */
        Collection::macro('paginate', function ($perPage, $page = null, $output_array = false, $total = null, $pageName = 'page') {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

            $items = $this->forPage($page, $perPage);

            if ($output_array) { // make sure no start indexes > 0 are provided, so output is not rendered as object, but as array of objects
                $items = Collection::make(array_values($items->toArray()));
            }

            $paginator = new LengthAwarePaginator(
                $items,
                $total ?: $this->count(),
                $perPage,
                $page,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );

            return $paginator;
        });

        $this->bootRoute();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(HiveFactory::class, function () {
            return new HiveFactory;
        });

        $this->app->singleton(ChecklistFactory::class, function () {
            return new ChecklistFactory;
        });

        // if needed install crud generator package compatible with laravel 13+, such as ibex/crud-generator, and register it here for local environment
        // if ($this->app->environment() == 'local') {
        //     $this->app->register('Appzcoder\CrudGenerator\CrudGeneratorServiceProvider');
        // }
    }

    public function bootRoute(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}

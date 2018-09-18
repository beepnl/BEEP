<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\HiveFactory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(HiveFactory::class, function() 
        {
            return new HiveFactory();  
        });

        $this->app->singleton(ChecklistFactory::class, function() 
        {
            return new ChecklistFactory();  
        });

        if ($this->app->environment() == 'local') 
        {
            $this->app->register('Appzcoder\CrudGenerator\CrudGeneratorServiceProvider');
        }
    }
}

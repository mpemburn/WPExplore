<?php

namespace App\Providers;

use App\Helpers\Curl;
use App\Helpers\Database;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('curl',function() {
            return new Curl();
        });
        $this->app->bind('database',function(){
            return new Database();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}

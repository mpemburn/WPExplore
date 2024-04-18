<?php

namespace App\Providers;

use App\Facades\Csv;
use App\Helpers\Curl;
use App\Helpers\Database;
use App\Helpers\Reader;
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
        $this->app->bind('reader',function(){
            return new Reader();
        });
        $this->app->bind('curl',function() {
            return new Curl();
        });
        $this->app->bind('csv',function() {
            return new Csv();
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

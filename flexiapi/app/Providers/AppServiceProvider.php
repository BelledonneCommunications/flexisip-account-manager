<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Validator::extend('iso_date', 'validateIsoDate');

        if (!empty(config('app.url'))) {
            // Add following lines to force laravel to use APP_URL as root url for the app.
            $strBaseURL = $this->app['url'];
            $strBaseURL->forceRootUrl(config('app.url'));
        }
    }
}

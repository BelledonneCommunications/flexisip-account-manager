<?php

namespace App\Providers;

use App\Space;
use Illuminate\Support\Facades\Route;
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
        \Illuminate\Support\Facades\Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('keycloak', \SocialiteProviders\Keycloak\Provider::class);
        });

        // Protect all the {space} parameters in the routes if the user is admin and its not his own Space
        Route::bind('space', function (string $id) {
            if (request()->user()->admin && !request()->user()->superAdmin
            && $id != request()->user()->space->id) {
                abort(404);
            }

            return Space::where('id', $id)->firstOrFail();
        });
    }
}

<?php

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\AuthenticateAdmin;
use App\Http\Middleware\AuthenticateClientCertificate;
use App\Http\Middleware\AuthenticateDigestOrKey;
use App\Http\Middleware\AuthenticateJWT;
use App\Http\Middleware\AuthenticateSuperAdmin;
use App\Http\Middleware\CheckBlocked;
use App\Http\Middleware\IsCardDavCredentialsEnabled;
use App\Http\Middleware\IsIntercomFeatures;
use App\Http\Middleware\IsPhoneRegistration;
use App\Http\Middleware\IsPublicRegistration;
use App\Http\Middleware\IsWebPanelEnabled;
use App\Http\Middleware\Localization;
use App\Http\Middleware\SpaceCheck;
use App\Http\Middleware\ValidateJSON;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(SpaceCheck::class);
        $middleware->append(Localization::class);
        $middleware->api(append: [ValidateJSON::class]);

        $middleware->alias([
            'auth' => Authenticate::class,
            'auth.admin' => AuthenticateAdmin::class,
            'auth.super_admin' => AuthenticateSuperAdmin::class,
            'auth.check_blocked' => CheckBlocked::class,
            'auth.client_certificate' => AuthenticateClientCertificate::class,
            'auth.digest_or_key' => AuthenticateDigestOrKey::class,
            'auth.jwt' => AuthenticateJWT::class,
            'feature.carddav_user_credentials' => IsCardDavCredentialsEnabled::class,
            'feature.intercom' => IsIntercomFeatures::class,
            'feature.phone_registration' => IsPhoneRegistration::class,
            'feature.public_registration' => IsPublicRegistration::class,
            'feature.web_panel_enabled' => IsWebPanelEnabled::class,
        ]);
    })
    ->withProviders()
    ->withExceptions(function (Exceptions $exceptions) {})->create();



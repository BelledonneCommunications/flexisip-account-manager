<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Flexisip Account Manager'),

    'project_url' => env('APP_PROJECT_URL', ''),
    'terms_of_use_url' => env('TERMS_OF_USE_URL', ''),
    'privacy_policy_url' => env('PRIVACY_POLICY_URL', ''),

    'allow_phone_number_username_admin_api' => env('APP_ALLOW_PHONE_NUMBER_USERNAME_ADMIN_API', false),
    'account_blacklisted_usernames' => env('ACCOUNT_BLACKLISTED_USERNAMES', ''),
    'account_email_unique' => env('ACCOUNT_EMAIL_UNIQUE', false),
    'account_username_regex' => env('ACCOUNT_USERNAME_REGEX', '^[a-z0-9+_.-]*$'),
    'account_default_password_algorithm' => env('ACCOUNT_DEFAULT_PASSWORD_ALGORITHM', 'SHA-256'),
    'account_authentication_bearer' => env('ACCOUNT_AUTHENTICATION_BEARER', null),

    /**
     * Time limit before the API Key and related cookie are expired
     */
    'api_key_expiration_minutes' => env('APP_API_KEY_EXPIRATION_MINUTES', 60),
    'account_creation_token_expiration_minutes' => env('APP_ACCOUNT_CREATION_TOKEN_EXPIRATION_MINUTES', 0),
    'account_recovery_token_expiration_minutes' => env('APP_ACCOUNT_RECOVERY_TOKEN_EXPIRATION_MINUTES', 0),
    'email_change_code_expiration_minutes' => env('APP_EMAIL_CHANGE_CODE_EXPIRATION_MINUTES', 10),
    'phone_change_code_expiration_minutes' => env('APP_PHONE_CHANGE_CODE_EXPIRATION_MINUTES', 10),
    'recovery_code_expiration_minutes' => env('APP_RECOVERY_CODE_EXPIRATION_MINUTES', 10),
    'provisioning_token_expiration_minutes' => env('APP_PROVISIONING_TOKEN_EXPIRATION_MINUTES', 0),
    'reset_password_email_token_expiration_minutes' => env('APP_RESET_PASSWORD_EMAIL_TOKEN_EXPIRATION_MINUTES', 1440),
    /**
     * Amount of minutes before re-authorizing the generation of a new account creation token
     */
    'account_creation_token_retry_minutes' => env('APP_API_ACCOUNT_CREATION_TOKEN_RETRY_MINUTES', 60),

    /**
     * CoTURN authentication in the provisioning
     */
    'coturn_server_host' => env('COTURN_SERVER_HOST', null),
    'coturn_session_ttl_minutes' => (int)env('COTURN_SESSION_TTL_MINUTES', 60 * 24),
    'coturn_static_auth_secret' => env('COTURN_STATIC_AUTH_SECRET', null),

    /**
     * External interfaces
     */
    'flexisip_pusher_path' => env('APP_FLEXISIP_PUSHER_PATH', null),
    'flexisip_pusher_firebase_keysmap' => env('APP_FLEXISIP_PUSHER_FIREBASE_KEYSMAP', null),
    'linphone_daemon_unix_pipe' => env('APP_LINPHONE_DAEMON_UNIX_PATH', null),

    /**
     * Blocking service
     */
    'blocking_time_period_check' => env('BLOCKING_TIME_PERIOD_CHECK', 30),
    'blocking_amount_events_authorized_during_period' => env('BLOCKING_AMOUNT_EVENTS_AUTHORIZED_DURING_PERIOD', 5),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),
    'root_host' => env('APP_ROOT_HOST', null),
    'asset_url' => env('ASSET_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',
    'authorized_locales' => ['en', 'fr'],

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\HelperServiceProvider::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Arr' => Illuminate\Support\Arr::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'HCaptcha' => Scyllaly\HCaptcha\Facades\HCaptcha::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'Str' => Illuminate\Support\Str::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        //'Utils' => App\Helpers\class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,
    ],

];

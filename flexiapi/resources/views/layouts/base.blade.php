<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
        @if (config('instance.custom_theme'))
            @if (file_exists(public_path('css/'.config('app.env').'.style.css')))
                <link rel="stylesheet" type="text/css" href="{{ asset('css/'.config('app.env').'.style.css') }}" >
            @else
                <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}" >
            @endif
        @endif
    </head>
    <body>
        <header>
            @yield('header')
        </header>
        @yield('body')
        <footer class="text-center mt-2">
            @if (config('instance.copyright'))
                {{ config('instance.copyright') }} |
            @endif
            <a href="{{ route('api') }}">API</a>
        </footer>
    </body>
</html>

<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }}</title>
    <!--<link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">-->

    @if (config('instance.custom_theme'))
        @if (file_exists(public_path('css/' . config('app.env') . '.style.css')))
            <link rel="stylesheet" type="text/css" href="{{ asset('css/' . config('app.env') . '.style.css') }}">
        @else
            <link rel="stylesheet" type="text/css" href="{{ asset('css/far.css') }}">
        @endif
        <!--<link rel="stylesheet" type="text/css" href="{{ asset('css/charts.css') }}" >-->
    @endif
</head>

<body class="@yield('classes')">
    <header>
        @yield('header')
    </header>
    @yield('body')
    <!--
    <footer class="text-center mt-2">

        @if (config('instance.copyright'))
            {{ config('instance.copyright') }} |
        @endif
        <a href="{{ route('account.documentation') }}">Documentation</a> |
        <a href="{{ route('api') }}">API</a>
    </footer>-->
</body>

</html>

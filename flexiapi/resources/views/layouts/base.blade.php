<!DOCTYPE html>

@php $configuration = \App\Configuration::first() @endphp

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
        @if ($configuration && $configuration->custom_theme)
            <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}" >
        @endif
    </head>
    <body>
        <header>
            @yield('header')
        </header>
        @yield('body')
        <footer class="text-center mt-2">
            @if ($configuration)
                {{ $configuration->copyright }}
            @endif
        </footer>
    </body>
</html>

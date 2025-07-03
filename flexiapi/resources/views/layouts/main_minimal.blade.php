<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('code') - @yield('title')</title>

    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">

    <script src="{{ asset('scripts/utils.js') }}"></script>
    <script src="{{ asset('scripts/chart.js') }}"></script>
    <script src="{{ asset('scripts/chartjs-plugin-datalabels@2.0.0') }}"></script>
</head>

<body class="@if (isset($welcome) && $welcome) welcome @endif">
    <content>
        <section>
            @yield('content')
        </section>
    </content>
</body>

</html>

<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }}</title>

    @if (config('instance.custom_theme'))
        @if (file_exists(public_path('css/' . config('app.env') . '.style.css')))
            <link rel="stylesheet" type="text/css" href="{{ asset('css/' . config('app.env') . '.style.css') }}">
        @else
            <link rel="stylesheet" type="text/css" href="{{ asset('css/far.css') }}">
        @endif
        <!--<link rel="stylesheet" type="text/css" href="{{ asset('css/charts.css') }}" >-->
    @endif
</head>

<body class="@if (isset($welcome) && $welcome) welcome @endif">
    <header>
        @if (config('app.web_panel'))
            <nav>
                <a href="{{ route('account.home') }}">{{ config('app.name') }}</a>
                @if (auth()->user())
                    <a href="{{ route('account.dashboard') }}">
                        <i class="material-icons">person</i>My Account
                    </a>
                    <a href="{{ route('account.logout') }}">
                        <i class="material-icons">logout</i>
                    </a>
                @else
                    <a href="{{ route('account.login') }}">
                        <i class="material-icons">info</i> Login
                    </a>
                @endif
            </nav>
        @endif
    </header>

    <content>
        @if (!isset($welcome) || $welcome == false)
            @include('parts.sidebar')
        @endif

        @include('parts.errors')

        @if (!isset($welcome) || $welcome == false)
            <section>
        @endif

        @yield('content')

        @if (!isset($welcome) || $welcome == false)
            </section>
        @endif
    </content>
</body>

</html>

<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }}</title>

    <script src="{{ asset('scripts/utils.js') }}""></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/far.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/form.css') }}">

    @if (config('instance.custom_theme') & file_exists(public_path('css/' . config('app.env') . '.style.css')))
        <link rel="stylesheet" type="text/css" href="{{ asset('css/' . config('app.env') . '.style.css') }}">
        <!--<link rel="stylesheet" type="text/css" href="{{ asset('css/charts.css') }}" >-->
    @endif
</head>

<body class="@if (isset($welcome) && $welcome) welcome @endif">
    <header>
        @if (config('app.web_panel'))
            <nav>
                <a id="logo" href="{{ route('account.home') }}"><span
                        class="on_desktop">{{ config('app.name') }}</span></a>

                @if (!isset($welcome) || $welcome == false)
                    <a id="menu" class="on_mobile" href="#"
                        onclick="document.body.classList.toggle('show_menu')"></a>
                @endif

                <a class="oppose" href="{{ route('about') }}">
                    <i class="material-icons">info</i><span class="on_desktop">About</span>
                </a>
                @if (auth()->user())
                    <a class="oppose" href="{{ route('account.dashboard') }}">
                        <i class="material-icons">account_circle</i><span
                            class="on_desktop">{{ auth()->user()->identifier }}</span>
                    </a>
                    <a class="oppose" href="{{ route('account.logout') }}">
                        <i class="material-icons">logout</i>
                    </a>
                @else
                    <a class="oppose" href="{{ route('account.login') }}">
                        <i class="material-icons">info</i><span class="on_desktop">Login</span>
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

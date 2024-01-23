<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }}</title>

    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">
    @if (config('instance.custom_theme') & file_exists(public_path('css/' . config('app.env') . '.style.css')))
        <link rel="stylesheet" type="text/css" href="{{ asset('css/' . config('app.env') . '.style.css') }}">
    @endif

    <script src="{{ asset('scripts/utils.js') }}""></script>
    <script src="{{ asset('scripts/chart.js') }}"></script>
    <script src="{{ asset('scripts/chartjs-plugin-datalabels@2.0.0') }}"></script>
</head>

<body class="@if (isset($welcome) && $welcome) welcome @endif">
    <header>
        <nav>
            <a id="logo" href="{{ route('account.home') }}"><span
                    class="on_desktop">{{ config('app.name') }}</span></a>

            @if (!isset($welcome) || $welcome == false)
                <a id="menu" class="on_mobile" href="#"
                    onclick="document.body.classList.toggle('show_menu')"></a>
            @endif

            <a class="oppose" href="{{ route('about') }}">
                <i class="material-symbols-outlined">info</i><span class="on_desktop">About</span>
            </a>
            @if (auth()->user())
                <a class="oppose" href="{{ route('account.dashboard') }}">
                    <i class="material-symbols-outlined">account_circle</i><span
                        class="on_desktop">{{ auth()->user()->identifier }}</span>
                </a>
                <a class="oppose" href="{{ route('account.logout') }}">
                    <i class="material-symbols-outlined">logout</i>
                </a>
            @endif
        </nav>
    </header>

    <content>
        @if (!isset($welcome) || $welcome == false)
            @include('parts.sidebar')
        @endif

        @include('parts.errors')

        @if (!isset($welcome) || $welcome == false)
            <section @if (isset($grid) && $grid) class="grid" @endif>

            @hasSection('breadcrumb')
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('account.dashboard') }}">Dashboard</a></li>
                        @yield('breadcrumb')
                    </ol>
                </nav>
            @endif
        @endif

        @yield('content')

        @if (!isset($welcome) || $welcome == false)
            </section>
        @endif
    </content>
</body>

</html>

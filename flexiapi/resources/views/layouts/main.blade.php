<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ space()->name }}</title>

    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">

    @php
        $space = space();
    @endphp

    @if (space()?->custom_theme && file_exists(public_path('css/' . space()?->host . '.style.css')))
        <link rel="stylesheet" type="text/css" href="{{ asset('css/' . space()?->host . '.style.css') }}">
    @endif

    <script src="{{ asset('scripts/utils.js') }}"></script>
    <script src="{{ asset('scripts/chart.js') }}"></script>
    <script src="{{ asset('scripts/chartjs-plugin-datalabels@2.0.0') }}"></script>
</head>

<body class="@if (isset($welcome) && $welcome) welcome @endif">
    <header>
        <nav>
            <a id="logo" href="{{ route('account.home') }}"><span
                    class="on_desktop">{{ space()->name }}</span></a>

            @if (!isset($welcome) || $welcome == false)
                <a id="menu" class="on_mobile" href="#"
                    onclick="document.body.classList.toggle('show_menu')"></a>
            @endif

            @if (auth()->user())
                <a class="oppose" href="{{ route('account.dashboard') }}">
                    <i class="ph ph-user"></i>
                    <span class="on_desktop">{{ auth()->user()->identifier }}</span>

                    @if (auth()->user()->superAdmin)
                        <span class="badge badge-error" title="Admin">Super Adm.</span>
                    @elseif (auth()->user()->admin)
                        <span class="badge badge-primary" title="Admin">Adm.</span>
                    @endif
                </a>
            @endif

            <a class="oppose" href="{{ route('about') }}">
                <i class="ph ph-info</i>"><span class="on_desktop">{{ __('About') }}</span>
            </a>

            @if (auth()->user())
                <a class="oppose" href="{{ route('account.logout') }}">
                    <i class="ph ph-sign-out"></i>
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

@extends('layouts.base')

@section('header')
    <nav class="navbar navbar-expand-lg">
        <div class="collapse navbar-collapse" >
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="/">{{ config('app.name') }}</a>
                </li>
                @if (isset($user) && get_class($user) == 'App\Account')
                    <li class="nav-item active">
                        <a class="nav-link" href="{{ route('account.panel') }}">My Account</a>
                    </li>
                @endif
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item @if (request()->routeIs('account.register')) active @endif">
                    <a class="nav-link" href="{{ route('account.register') }}">Register</a>
                </li>
                <li class="nav-item @if (request()->routeIs('account.login')) active @endif">
                    <a class="nav-link" href="{{ route('account.login') }}">Login</a>
                </li>
            </ul>
        </div>
    </nav>
@endsection

@section('body')
    <div class="container pt-4">
        @include('parts.errors')
        @yield('content')
    </div>
@endsection
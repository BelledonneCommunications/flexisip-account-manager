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
                        <a class="nav-link" href="{{ route('account.index') }}">My Account</a>
                    </li>
                @endif
                <li class="nav-item @if (request()->routeIs('api')) active @endif">
                    <a class="nav-link" href="{{ route('api') }}">API</a>
                </li>
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item @if (request()->routeIs('account.register')) active @endif">
                    <a class="nav-link" href="{{ route('account.register') }}">Register</a>
                </li>
                <li class="nav-item @if (request()->routeIs('account.login')) active @endif">
                    <a class="nav-link" href="{{ route('account.login') }}">Authenticate</a>
                </li>
            </ul>
        </div>
    </nav>
@endsection

@section('body')
    <div class="container-lg pt-3">
        @include('parts.errors')
        @yield('content')
    </div>
@endsection
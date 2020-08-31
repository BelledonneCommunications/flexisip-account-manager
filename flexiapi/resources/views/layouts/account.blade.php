@extends('layouts.base')

@section('header')
    <nav class="navbar navbar-expand">
        <div class="collapse navbar-collapse" >
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="{{ route('account.panel') }}">{{ config('app.name') }}</a>
                </li>
            </ul>
            @if (Auth::check())
                <ul class="navbar-nav">
                    <li class="nav-item active">
                        <a class="nav-link" href="{{ route('account.logout') }}">Logout</a>
                    </li>
                </ul>
            @endif
        </div>
    </nav>
@endsection

@section('body')
    <div class="container pt-4">
        @include('parts.errors')
        @include('parts.breadcrumb')
        @yield('content')
    </div>
@endsection
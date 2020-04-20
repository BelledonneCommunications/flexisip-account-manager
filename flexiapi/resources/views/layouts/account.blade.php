@extends('layouts.base')

@section('body')
    <nav class="navbar navbar-expand navbar-light bg-light">
        <div class="collapse navbar-collapse" >
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="{{ route('account.index') }}">{{ config('app.name') }}</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item active">
                    <a class="nav-link" href="{{ route('account.logout') }}">Logout</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container-lg pt-3">
        @include('parts.errors')
        @yield('content')
    </div>
@endsection
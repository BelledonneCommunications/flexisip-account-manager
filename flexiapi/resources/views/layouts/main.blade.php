@extends('layouts.base')

@section('body')
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
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
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('api') }}">API</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container-lg pt-3">
        @include('parts.errors')
        @yield('content')
    </div>
@endsection
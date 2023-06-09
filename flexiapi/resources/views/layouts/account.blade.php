@extends('layouts.base')

@section('header')
    <nav>
        <a href="{{ route('account.dashboard') }}">{{ config('app.name') }}</a>
        @if (Auth::check())
            <a href="{{ route('account.logout') }}">Logout</a>
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

@extends('layouts.main')

@section('content')

<h2 class="text-center pt-5">@yield('code') - @yield('title')</h2>

<p class="text-center">
    @yield('message')
    <br />
    <a class="btn btn-secondary mt-5" href="{{ route('account.home') }}">
        Go back to the homepage
    </a>
</p>

@endsection
@extends(space() != null ? 'layouts.main' : 'layouts.main_minimal')

@section('content')

<h2>@yield('code') - @yield('title')</h2>

<p class="text-center">
    @yield('message')
    <br /><br />
    <a class="btn secondary mt-5" href="{{ route('account.home') }}">
        Go back to the homepage
    </a>
</p>

@endsection
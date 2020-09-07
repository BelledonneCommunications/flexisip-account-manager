@extends('layouts.main')

@section('content')
    @if (Auth::check())
        @include('parts.already_auth')
    @else
        @if ($account->activated)
            <p>A unique authentication link was sent by email to <b>{{ $account->email }}</b>.</p>
        @else
            <p>To finish your registration process and set a password please follow the link sent on your email address <b>{{ $account->email }}</b>.</p>
        @endif
    @endif
@endsection
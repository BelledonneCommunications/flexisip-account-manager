@extends('layouts.main')

@section('content')
    @if (Auth::check())
        @include('parts.already_auth')
    @else
<p>A unique authentication link was sent by email to {{ $account->email }}</p>
    @endif
@endsection
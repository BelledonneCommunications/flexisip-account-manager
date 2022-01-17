@extends('layouts.main')

@section('content')

<h2>{{ config('app.name') }}</h2>

<p>There are <b>{{ number_format($count) }} users</b> registered with this service.</p>

@if (config('instance.intro_registration'))
    @parsedown(config('instance.intro_registration'))
@endif

@if (config('app.web_panel'))
    <hr />

    <div class="list-group mb-3">
        @if (config('app.public_registration'))
            <a href="{{ route('account.register') }}" class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">Create an account</h5>
                </div>
                <p class="mb-1">Register on our service</p>
            </a>
        @endif
        <a href="{{ route('account.login') }}" class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1">Manage your account</h5>
            </div>
            <p class="mb-1">Get access to your account panel to configure it</p>
        </a>
    </div>
@endif

@include('parts.password_recovery')

@endsection
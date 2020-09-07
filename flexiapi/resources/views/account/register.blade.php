@extends('layouts.main')

@section('content')

<p class="text-center">
    You already have an account?
    <a class="ml-2 btn btn-primary btn-sm" href="{{ route('account.login') }}">Login</a>
</p>

<hr />

<h2>Register a new account</h2>

<div class="list-group mb-3 pt-2">
    <a href="{{ route('account.register.email') }}" class="list-group-item list-group-item-action">
        <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1">Using your email address</h5>
        </div>
        <p class="mb-1">Register on our service with an email address</p>
    </a>
    <a href="{{ route('account.register.phone') }}" class="list-group-item list-group-item-action">
        <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1">With your phone number</h5>
        </div>
        <p class="mb-1">Use your phone number to register</p>
    </a>
</div>

@endsection
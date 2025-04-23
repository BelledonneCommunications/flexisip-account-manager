@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <h1><i class="ph">user-circle</i> {{ __('Register') }}</h1>
        <p style="margin-bottom: 2rem;">
            {{ __('You already have an account?') }}
            <a class="btn secondary" href="{{ route('account.login') }}">{{ __('Login') }}</a>
        </p>
        @include('parts.tabs.register')

        <form method="POST" action="{{ route('account.store') }}" accept-charset="UTF-8">
@csrf

        <div>
            <input placeholder="username" name="username" type="text" value="{{ old('username') }}">
            <label for="username">{{ __('Username') }}</label>
            <small>{{ __('In lowercase letters') }}</small>
            @include('parts.errors', ['name' => 'username'])
        </div>
        <div>
            <input type="text" name="domain" value="{{ $domain }}" disabled>
        </div>

        <div>
            <input placeholder="+123456789" name="phone" type="text" value="{{ old('phone') }}">
            <label for="phone">{{ __('Phone number') }}</label>
            @include('parts.errors', ['name' => 'phone'])
        </div>
        <div></div>

        <div>
            <input required="" name="password" type="password" value="" placeholder="{{ __('Password') }}">
            <label for="password">{{ __('Password') }}</label>
            @include('parts.errors', ['name' => 'password'])
        </div>
        <div>
            <input required="" name="password_confirmation" type="password" value=""
                placeholder="Password confirmation">
            <label for="password_confirmation">{{ __('Confirm password') }}</label>
            @include('parts.errors', ['name' => 'password_confirmation'])
        </div>

        @include('parts.terms')

        <div class="large">
            <input class="btn oppose" type="submit" value="{{ __('Register') }}">
        </div>

        </form>

    </section>
    <section class="on_desktop">
        <img src="{{ asset('img/login.svg') }}">
    </section>
@endsection

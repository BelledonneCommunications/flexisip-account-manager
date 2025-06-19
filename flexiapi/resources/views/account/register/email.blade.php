@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <h1><i class="ph ph-user-circle"></i> {{ __('Register') }}</h1>
        <p style="margin-bottom: 2rem;">
            {{ __('You already have an account?') }}
            <a class="btn secondary" href="{{ route('account.login') }}">{{ __('Login') }}</a>
        </p>
        @include('parts.tabs.register')

        <form method="POST" action="{{ route('account.store') }}" accept-charset="UTF-8">
        @csrf

        <div>
            <input placeholder="username" name="username" type="text" value="{{ old('username') }}" required>
            <label for="username">{{ __('Username') }}</label>
            <small>{{ __('In lowercase letters') }}</small>
            @include('parts.errors', ['name' => 'username'])
        </div>

        <div>
            <input type="text" name="domain" value="{{ $domain }}" disabled>
        </div>

        <div>
           <input type="email" name="email" value="{{ old('email') }}" placeholder="bob@example.net" required>
            <label for="email">{{ __('Email') }}</label>
            @include('parts.errors', ['name' => 'email'])
        </div>
        <div>
           <input type="email" name="email_confirmation" value="{{ old('email_confirm') }}" placeholder="bob@example.net" required>
            <label for="email_confirmation">{{ __('Confirm email') }}</label>
            @include('parts.errors', ['name' => 'email_confirmation'])
        </div>

        <div>
            <input required="" name="password" type="password" value="{{ old('password') }}" placeholder="{{ __('Password') }}">
            <label for="password">{{ __('Password') }}</label>
            @include('parts.errors', ['name' => 'password'])
        </div>
        <div>
            <input required="" name="password_confirmation" type="password" value="{{ old('password_confirmation') }}" placeholder="{{ __('Confirm password') }}">
            <label for="password_confirmation">{{ __('Confirm password') }}</label>
            @include('parts.errors', ['name' => 'password_confirmation'])
        </div>

        @if (!empty(config(space()?->newsletter_registration_address)))
            <div class="large checkbox">
                <input id="newsletter" name="newsletter" type="checkbox" value="true">
                <label for="newsletter"></label>
                <div>
                    <p>{{ __('I would like to subscribe to the newsletter') }}</p>
                </div>
            </div>
        @endif

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

@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <h1>
            <i class="ph">envelope</i>
            {{ __('Change your email') }}
        </h1>

        <form method="POST" action="{{ route('account.email.request_change') }}" accept-charset="UTF-8">
            @csrf

            <div class="large">
                <p>{{ __('Please enter the new email that you would like to link to your account.') }}</p>

                @include('parts.errors', ['name' => 'code'])
            </div>

            <div class="large">
                <input type="email" name="email" value="" placeholder="email@server.tld" required>
                <label for="email">{{ __('Email') }}</label>
                @include('parts.errors', ['name' => 'email'])
            </div>

            @include('parts.captcha')

            <div class="large">
                <input class="btn oppose" type="submit" value="{{ __('Verify') }}">
            </div>
        </form>

    </section>
    <section class="on_desktop">
        <img src="{{ asset('img/lock.svg') }}">
    </section>
@endsection


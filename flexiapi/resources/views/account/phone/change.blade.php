@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <h1>
            <i class="ph ph-phone"></i>
            {{ __('Change your phone number') }}
        </h1>

        <form method="POST" action="{{ route('account.phone.request_change') }}" accept-charset="UTF-8">
            @csrf

            <div class="large">
                <p>{{ __('Please enter the new phone number that you would like to link to your account.') }}</p>

                @include('parts.errors', ['name' => 'code'])
            </div>

            <div class="large">
                <input placeholder="+12345678" name="phone" type="text" value="">
                <label for="phone">{{ __('Phone number') }}</label>
                @include('parts.errors', ['name' => 'phone'])
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

@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <h1><i class="ph">user-circle</i> {{ __('Account recovery') }}</h1>
        <div>
            <form method="POST" action="{{ route('account.recovery.send') }}" accept-charset="UTF-8">
                @csrf

                @if ($method == 'email')
                    <div class="large">
                        @if (config('app.recovery_code_expiration_minutes') > 0)
                            <p class="large">
                                {{ __('The code will be available :minutes minutes.', ['minutes' => config('app.recovery_code_expiration_minutes')]) }}
                            </p>
                        @endif
                        @include('parts.errors', ['name' => 'code'])
                    </div>
                    <div class="large">
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="bob@example.com" required>
                        <label for="email">{{ __('Email') }}</label>
                        @include('parts.errors', ['name' => 'email'])
                        @include('parts.errors', ['name' => 'identifier'])
                    </div>

                    @if (config('app.account_email_unique') == false)
                        <div>
                            <input placeholder="username" name="username" type="text" value="{{ old('username') }}">
                            <label for="username">{{ __('Username') }}</label>
                        </div>
                        <div>
                            <input type="text" name="username" value="{{ $domain }}" disabled>
                        </div>
                    @endif
                @elseif($method == 'phone')
                    @if (config('app.recovery_code_expiration_minutes') > 0)
                        <p class="large">
                            {{ __('The code will be available :minutes minutes.', ['minutes' => config('app.recovery_code_expiration_minutes')]) }}
                        </p>
                    @endif
                    <div>
                        <input placeholder="+123456789" name="phone" type="text" value="{{ old('phone') }}">
                        <label for="phone">{{ __('Phone number') }}</label>
                        @include('parts.errors', ['name' => 'phone'])
                        @include('parts.errors', ['name' => 'identifier'])
                    </div>
                @endif

                @include('parts.captcha')

                <div class="large">
                    <input class="btn oppose" type="submit" value="{{ __('Send')}}">
                </div>
            </form>
        </div>
    </section>
    <section class="on_desktop">
        <img src="{{ asset('img/lock.svg') }}">
    </section>
@endsection

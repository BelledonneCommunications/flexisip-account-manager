@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <h1><i class="ph ph-user-circle"></i> {{ __('Account recovery') }}</h1>
        <div>
            <form method="POST" action="{{ route('account.recovery.send') }}" accept-charset="UTF-8">
                @csrf

                @if ($method == 'email')
                    <div class="large">
                        @if (config('app.recovery_code_expiration_minutes') > 0)
                            <p class="large">
                                {{ __('We will send you a verification code to recover your account.') }}
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
                            {{ __('We will send you a verification code to recover your account.') }}
                        </p>
                    @endif
                    <div>
                        <input placeholder="+123456789" name="phone" type="text" value="@if ($phone){{ $phone }}@else{{ old('phone') }}@endif">
                        <label for="phone">{{ __('Phone number') }}</label>
                        @include('parts.errors', ['name' => 'phone'])
                        @include('parts.errors', ['name' => 'identifier'])
                    </div>
                @endif

                @if (!empty($account_recovery_token))
                    <input name="account_recovery_token" type="hidden" value="{{ $account_recovery_token }}">
                @else
                    @include('parts.captcha')
                @endif

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

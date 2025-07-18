@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <h1 style="margin-bottom: 3rem;"><i class="ph ph-hand-waving"></i> {{ __('Welcome on :app_name' , ['app_name' => space()->name]) }}</h1>

        @if (space()->intro_registration_text)
            @parsedown(space()->intro_registration_text)
        @endif

        <form style="margin-top: 3rem; margin-bottom: 3rem;" method="POST" action="{{ route('account.authenticate') }}" accept-charset="UTF-8">
            @csrf
            <div>
                @if (space()->phone_registration)
                    <input placeholder="{{ __('Username or Phone') }}" required="" name="username" type="text"
                        value="{{ old('username') }}">
                    <label for="username">{{ __('Username') }}</label>
                @else
                    <input placeholder="username" required="" name="username" type="text"
                        value="{{ old('username') }}">
                    <label for="username">{{ __('Username') }}</label>
                @endif
                @include('parts.errors', ['name' => 'authentication'])
            </div>
            <div class="on_desktop"></div>
            <div>
                <input placeholder="{{ __('Your password') }}" required="" name="password" type="password" value="">
                <label for="password">{{ __('Password') }}</label>
            </div>
            <div>
                <input class="btn" type="submit" value="{{ __('Login') }}">
            </div>

        </form>

        <br />

        @include('parts.recovery')

        @if (space()->public_registration)
            <br />
            <br />

            <p>
                {{ __('No account yet?') }}
                <a class="btn secondary" href="{{ route('account.register') }}">{{ __('Register') }}</a>
            </p>
        @endif
    </section>
    <section class="on_desktop" style="text-align: center;">
        <span style="color: var(--main-5); font-size: 5rem; font-weight: 300;">{{ $count }}</span><br />
        <p style="margin-bottom: 3rem;">users</p>
        <img src="{{ asset('img/login.svg') }}">
    </section>

@endsection

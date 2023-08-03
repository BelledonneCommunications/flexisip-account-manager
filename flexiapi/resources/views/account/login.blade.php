@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <h1 style="margin-bottom: 3rem;"><i class="material-icons">waving_hand</i> Welcome on {{ config('app.name') }}</h1>

        @if (config('instance.intro_registration'))
            @parsedown(config('instance.intro_registration'))
        @endif

        @if (Auth::check())
            @include('parts.already_auth')
        @else
            <form style="margin-top: 3rem; margin-bottom: 3rem;" method="POST" action="{{ route('account.authenticate') }}" accept-charset="UTF-8">
                @csrf
                <div>
                    @if (config('app.phone_authentication'))
                        <input placeholder="username or phone number" required="" name="username" type="text"
                            value="{{ old('username') }}">
                        <label for="username">Username or phone number</label>
                    @else
                        <input placeholder="username" required="" name="username" type="text"
                            value="{{ old('username') }}">
                        <label for="username">Username</label>
                    @endif
                </div>
                <div class="on_desktop"></div>
                <div>
                    <input placeholder="myPassword" required="" name="password" type="password" value="">
                    <label for="password">Password</label>
                </div>
                <div>
                    <input class="btn" type="submit" value="Login">
                </div>

            </form>

            <br />

            @include('parts.recovery')
        @endif

        @if (publicRegistrationEnabled())
            <br />
            <br />

            <p>
                No account yet?
                <a class="btn btn-secondary" href="{{ route('account.register') }}">Register</a>
            </p>
        @endif
    </section>
    <section class="on_desktop" style="text-align: center;">
        <span style="color: var(--main-5); font-size: 5rem; font-weight: 300;">{{ $count }}</span><br />
        <p style="margin-bottom: 3rem;">users</p>
        <img src="/img/login.svg">
    </section>

@endsection

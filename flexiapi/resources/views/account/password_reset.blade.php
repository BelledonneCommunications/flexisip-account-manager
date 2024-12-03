@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <header>
            <h1><i class="ph">lock</i> Reset password</h1>
        </header>

        @if ($token->offed())
            <p>This link is not available anymore.</p>
        @else
            <form id="password_update" method="POST" action="{{ route('account.reset_password_email.reset') }}" accept-charset="UTF-8">
                @csrf

                <input type="hidden" name="token" value="{{ $token->token }}">
                <div class="large">
                    <input type="password" name="password" required>
                    <label for="password">Password</label>
                    @include('parts.errors', ['name' => 'password'])
                </div>
                <div class="large">
                    <input type="password" name="password_confirmation" required>
                    <label for="password_confirmation">Password confirmation</label>
                    @include('parts.errors', ['name' => 'password_confirmation'])
                </div>

                @include('parts.captcha')

                <div class="large">
                    <input class="btn" type="submit" value="Reset">
                </div>
            </form>
        @endif
    </section>

    <section class="on_desktop">
        <img src="{{ asset('img/lock.svg') }}">
    </section>
@endsection

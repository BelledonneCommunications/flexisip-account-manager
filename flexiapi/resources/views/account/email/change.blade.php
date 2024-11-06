@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <h1>
            <i class="ph">envelope</i>
            @if ($account->email)
                Change your email
            @else
                Set your email
            @endif
        </h1>

        <form method="POST" action="{{ route('account.email.request_change') }}" accept-charset="UTF-8">
            @csrf

            <div class="large">
                @if ($account->email)
                    <p>Please enter the new email address that you would like to link to your account.</p>
                @else
                    <p>The verification code is invalid.</p>
                    <p>Please enter again your email address to receive a new code.</p>
                @endif

                @include('parts.errors', ['name' => 'code'])
            </div>

            <div class="large">
                <input type="email" name="email" value="" placeholder="email@server.tld" required>
                <label for="email">Email</label>
                @include('parts.errors', ['name' => 'email'])
            </div>

            @include('parts.captcha')

            <div class="large">
                <input class="btn oppose" type="submit" value="Verify">
            </div>
        </form>

    </section>
    <section class="on_desktop">
        <img src="{{ asset('img/lock.svg') }}">
    </section>
@endsection

@section('footer')
    Hop
@endsection

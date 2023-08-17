@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <h1>
            <i class="material-icons-outlined">call</i>
            @if ($account->phone)
                Change your phone number
            @else
                Set your phone number
            @endif
        </h1>

        <form method="POST" action="{{ route('account.phone.request_change') }}" accept-charset="UTF-8">
            @csrf

            <div class="large">
                @if ($account->phone)
                    <p>Please enter the new phone number that you would like to link to your account.</p>
                @else
                    <p>The verification code is invalid or you didn't receive it.</p>
                    <p>Please enter your phone number again to receive a new code.</p>
                @endif

                @include('parts.errors', ['name' => 'code'])
            </div>

            <div class="large">
                <input placeholder="+12345678" name="phone" type="text" value="">
                <label for="phone">Phone</label>
                @include('parts.errors', ['name' => 'phone'])
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

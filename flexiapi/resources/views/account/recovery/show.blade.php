@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <h1><i class="material-icons-outlined">account_circle</i> Account recovery</h1>
        <div>
            <form method="POST" action="{{ route('account.recovery.send') }}" accept-charset="UTF-8">
                @csrf

                @if ($method == 'email')
                    <div class="large">
                        <p class="large">Enter your email account to recover it.</p>
                        @include('parts.errors', ['name' => 'code'])
                    </div>
                    <div class="large">
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="bob@example.com" required>
                        <label for="email">Email</label>
                        @include('parts.errors', ['name' => 'email'])
                        @include('parts.errors', ['name' => 'identifier'])
                    </div>

                    @if (config('app.account_email_unique') == false)
                        <div>
                            <input placeholder="username" name="username" type="text" value="{{ old('username') }}">
                            <label for="username">Username</label>
                        </div>
                        <div>
                            <input type="text" name="username" value="{{ $domain }}" disabled>
                        </div>
                    @endif
                @elseif($method == 'phone')
                    <p class="large">Enter your phone number to recover your account.</p>
                    <div>
                        <input placeholder="+123456789" name="phone" type="text" value="{{ old('phone') }}">
                        <label for="phone">Phone</label>
                        @include('parts.errors', ['name' => 'phone'])
                        @include('parts.errors', ['name' => 'identifier'])
                    </div>
                @endif

                @include('parts.captcha')

                <div class="large">
                    <input class="btn oppose" type="submit" value="Send the code">
                </div>
            </form>
        </div>
    </section>
    <section class="on_desktop">
        <img src="{{ asset('img/lock.svg') }}">
    </section>
@endsection

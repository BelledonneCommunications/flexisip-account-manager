@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <h1 style="margin-bottom: 4rem;"><i class="ph">user-circle</i> {{ __('Code Verification') }}</h1>

        <form method="POST" action="{{ route('account.email.update') }}" accept-charset="UTF-8">
@csrf

        <div class="large">
            <p>{{ __('A verification code was sent by email to :email.', ['email' => $emailChangeCode->email]) }}</p>
            <p>{{ __('Enter the code you received below') }}</p>
        </div>

        <div class="large">
            <input oninput="digitFilled(this)" onfocus="this.value = ''" autofocus class="digit" name="number_1" type="number" min="0" max="9">
            <input oninput="digitFilled(this)" onfocus="this.value = ''" class="digit" name="number_2" type="number" min="0" max="9">
            <input oninput="digitFilled(this)" onfocus="this.value = ''" class="digit" name="number_3" type="number" min="0" max="9">
            <input oninput="digitFilled(this)" onfocus="this.value = ''" class="digit" name="number_4" type="number" min="0" max="9">
            @include('parts.errors', ['name' => 'code'])
        </div>

        <div>
            <input class="btn" type="submit" value="Validate">
        </div>

        </form>

        <div class="large" style="margin-top: 2rem;">
            <p>

                {{ __('You didn't receive the code?') }}
                <a class="btn secondary" href="{{ route('account.email.change') }}">{{ __('Resend') }}</a>
            </p>
        </div>

    </section>
    <section class="on_desktop">
        <img src="{{ asset('img/lock.svg') }}">
    </section>
@endsection


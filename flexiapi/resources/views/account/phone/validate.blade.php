@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <h1><i class="ph">user-circle</i> {{ __('Change your phone number') }}</h1>

        <form method="POST" action="{{ route('account.phone.update') }}" accept-charset="UTF-8">
        @csrf

        <div class="large">
            <p>{{ __('A verification code was sent by SMS to :phone.', ['phone' => $phoneChangeCode->phone]) }}</p>
            <p>{{ __('Enter the pin code bellow:') }}</p>
        </div>

        <div class="large">
            <input oninput="digitFilled(this)" autofocus class="digit" name="number_1" type="number" min="0" max="9">
            <input oninput="digitFilled(this)" class="digit" name="number_2" type="number" min="0" max="9">
            <input oninput="digitFilled(this)" class="digit" name="number_3" type="number" min="0" max="9">
            <input oninput="digitFilled(this)" class="digit" name="number_4" type="number" min="0" max="9">
            @include('parts.errors', ['name' => 'code'])
        </div>

        <div>
            <input class="btn" type="submit" value="Validate">
        </div>

        </form>

        <div class="large" style="margin-top: 2rem;">
            <p>
                {{ __("You didn't receive the code?"") }}
                <a class="btn btn-secondary" href="{{ route('account.phone.change') }}">{{ __('Resend') }}</a>
            </p>
        </div>

    </section>
    <section class="on_desktop">
        <img src="{{ asset('img/lock.svg') }}">
    </section>
@endsection


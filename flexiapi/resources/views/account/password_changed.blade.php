@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <header>
            <h1><i class="ph">lock</i> {{ __('Reset password') }}</h1>
        </header>

        <p>{{ __('Your password was updated properly.') }}</p>
        <p>
            <a class="btn" href="{{ route('account.login')}}">{{ __('Authenticate') }}</a>
        </p>
    </section>

    <section class="on_desktop">
        <img src="{{ asset('img/lock.svg') }}">
    </section>
@endsection

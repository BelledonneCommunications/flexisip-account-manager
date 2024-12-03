@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <header>
            <h1><i class="ph">lock</i> Reset password</h1>
        </header>

        <p>Your password was updated properly.</p>
        <p>
            <a class="btn" href="{{ route('account.login')}}">Authenticate</a>
        </p>
    </section>

    <section class="on_desktop">
        <img src="{{ asset('img/lock.svg') }}">
    </section>
@endsection

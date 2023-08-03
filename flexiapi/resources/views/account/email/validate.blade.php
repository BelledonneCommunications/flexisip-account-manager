@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <h1 style="margin-bottom: 4rem;"><i class="material-icons">account_circle</i> Validate your email</h1>

        {!! Form::open(['route' => 'account.email.update']) !!}

        <div class="large">
            <p>A verification code was sent by email on <b>{{ $emailChangeCode->email }}</b>.</p>
            <p>Please enter the verification code below:</p>
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

        {!! Form::close() !!}

        <div class="large" style="margin-top: 2rem;">
            <p>
                You didn't receive the code?
                <a class="btn btn-secondary" href="{{ route('account.email.change') }}">Resend a code</a>
            </p>
        </div>

    </section>
    <section class="on_desktop">
        <img src="/img/lock.svg">
    </section>
@endsection

@section('footer')
    Hop
@endsection

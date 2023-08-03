@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <h1><i class="material-icons">account_circle</i> Validate your phone number</h1>

        {!! Form::open(['route' => 'account.phone.update']) !!}

        <div class="large">
            <p>A verification code was sent by SMS on <b>{{ $phoneChangeCode->phone }}</b>.</p>
            <p>Please enter the verification code below:</p>
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

        {!! Form::close() !!}

        <div class="large" style="margin-top: 2rem;">
            <p>
                You didn't receive the code?
                <a class="btn btn-secondary" href="{{ route('account.phone.change') }}">Resend a code</a>
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

@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <h1>
            <i class="material-icons">mail</i>
            @if ($account->phone)
                Change your phone number
            @else
                Set your phone number
            @endif
        </h1>

        {!! Form::open(['route' => 'account.phone.request_change']) !!}

        <div class="large">
            @if ($account->phone)
                <p>Please enter the new phone number that you would like to link to your account.</p>
            @else
                <p>The verification code is invalid or you didn't receive it.</p>
                <p>Please enter your phone number again to receive a new code.</p>
            @endif
        </div>

        <div class="large">
            {!! Form::text('phone', null, ['placeholder' => '+12345678', 'required']) !!}
            {!! Form::label('phone', 'Phone') !!}
            @include('parts.errors', ['name' => 'phone'])
        </div>

        @include('parts.captcha')

        <div class="large">
            {!! Form::submit('Verify', ['class' => 'btn oppose']) !!}
        </div>

        {!! Form::close() !!}

    </section>
    <section class="on_desktop">
        <img src="/img/lock.svg">
    </section>
@endsection

@section('footer')
    Hop
@endsection

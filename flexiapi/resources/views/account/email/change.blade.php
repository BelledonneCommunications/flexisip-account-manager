@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <h1>
            <i class="material-icons">mail</i>
            @if ($account->email)
                Change your email
            @else
                Set your email
            @endif
        </h1>

        {!! Form::open(['route' => 'account.email.request_change']) !!}

        <div class="large">
            @if ($account->email)
                <p>Please enter the new email address that you would like to link to your account.</p>
            @else
                <p>The verification code is invalid.</p>
                <p>Please enter again your email address to receive a new code.</p>
            @endif
        </div>

        <div class="large">
            {!! Form::email('email', null, ['placeholder' => 'email@server.tld', 'required']) !!}
            {!! Form::label('email', 'Email') !!}
            @include('parts.errors', ['name' => 'email'])
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

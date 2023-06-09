@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <h1><i class="material-icons">account_circle</i> Validate your email</h1>

        {!! Form::open(['route' => 'account.email.update']) !!}

        <div class="large">
            <p>A verification code was sent by email on <b>{{ $emailChangeCode->email }}</b>.</p>
            <p>Please enter the verification code below:</p>
        </div>

        <div>
            {!! Form::number('code', null, ['placeholder' => '0000', 'required', 'min' => 0000, 'max' => 9999]) !!}
            {!! Form::label('code', 'Code') !!}
            @include('parts.errors', ['name' => 'code'])
        </div>

        <div>
            {!! Form::submit('Validate', ['class' => 'btn']) !!}
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

@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <h1><i class="material-icons">account_circle</i> Validate your phone number</h1>

        {!! Form::open(['route' => 'account.phone.update']) !!}

        <div class="large">
            <p>A verification code was sent by SMS on <b>{{ $phoneChangeCode->phone }}</b>.</p>
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

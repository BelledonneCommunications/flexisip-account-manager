@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <h1><i class="material-icons">account_circle</i> Account recovery</h1>
        <div>
            {!! Form::open(['route' => 'account.recovery.send']) !!}
                @if ($method == 'email')
                    <p class="large">Enter your email account to recover it.</p>
                    <div class="large">
                        {!! Form::email('email', old('email'), ['placeholder' => 'bob@example.com', 'required']) !!}
                        {!! Form::label('email', 'Email') !!}
                        @include('parts.errors', ['name' => 'email'])
                        @include('parts.errors', ['name' => 'identifier'])
                    </div>

                    @if (config('app.account_email_unique') == false)
                        <div>
                            {!! Form::text('username', old('username'), ['placeholder' => 'username', 'required']) !!}
                            {!! Form::label('username', 'Username') !!}
                        </div>
                        <div>
                            {!! Form::text('username', $domain, ['disabled']) !!}
                        </div>
                    @endif
                @elseif($method == 'phone')
                    <p class="large">Enter your phone number to recover your account.</p>
                    <div>
                        {!! Form::text('phone', old('phone'), ['placeholder' => '+123456789', 'required']) !!}
                        {!! Form::label('phone', 'Phone') !!}
                        @include('parts.errors', ['name' => 'phone'])
                        @include('parts.errors', ['name' => 'identifier'])
                    </div>
                @endif

                @include('parts.captcha')

                <div class="large">
                    {!! Form::submit('Send the code', ['class' => 'btn oppose']) !!}
                </div>
            {!! Form::close() !!}
        </div>
    </section>
    <section class="on_desktop">
        <img src="/img/lock.svg">
    </section>
@endsection
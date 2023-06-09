@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <h1><i class="material-icons">waving_hand</i> Oh hi!</h1>

        @if (config('instance.intro_registration'))
            @parsedown(config('instance.intro_registration'))
        @endif

        @if (Auth::check())
            @include('parts.already_auth')
        @else
            {!! Form::open(['route' => 'account.authenticate']) !!}
            <div class="large">
                @if (config('app.phone_authentication'))
                    {!! Form::text('username', old('username'), ['placeholder' => 'username or phone number', 'required']) !!}
                    {!! Form::label('username', 'Username or phone number') !!}
                @else
                    {!! Form::text('username', old('username'), ['placeholder' => 'username', 'required']) !!}
                    {!! Form::label('username', 'Username') !!}
                @endif
            </div>
            <div class="large">
                {!! Form::password('password', ['placeholder' => 'myPassword', 'required']) !!}
                {!! Form::label('password', 'Password') !!}
            </div>
            <div class="large">
                {!! Form::submit('Login', ['class' => 'btn oppose']) !!}
            </div>

            {!! Form::close() !!}

            <br />

            @include('parts.recovery')
        @endif

        @if (publicRegistrationEnabled())
            <br />
            <br />

            <p>
                No account yet?
                <a class="btn btn-secondary" href="{{ route('account.register') }}">Register</a>
            </p>
        @endif
    </section>
    <section class="on_desktop" style="text-align: center;">
        <span style="color: var(--main-5); font-size: 5rem; font-weight: 300;">{{ $count }}</span><br />
        <p style="margin-bottom: 3rem;">users</p>
        <img src="/img/login.svg">
    </section>

@endsection

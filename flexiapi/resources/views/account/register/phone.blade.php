@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <h1><i class="material-icons">account_circle</i> Register</h1>
        <p style="margin-bottom: 2rem;">
            You already have an account?
            <a class="btn btn-secondary" href="{{ route('account.login') }}">Login</a>
        </p>
        @include('parts.tabs.register')

        {!! Form::open(['route' => 'account.store']) !!}

        <div>
            {!! Form::text('username', old('username'), ['placeholder' => 'username', 'required']) !!}
            {!! Form::label('username', 'Username') !!}
            @include('parts.errors', ['name' => 'username'])
        </div>
        <div>
            <input type="text" name="domain" value="{{ $domain }}" disabled>
        </div>

        <div>
            {!! Form::text('phone', old('phone'), ['placeholder' => '+123456789', 'required']) !!}
            {!! Form::label('phone', 'Phone number') !!}
            @include('parts.errors', ['name' => 'phone'])
        </div>
        <div></div>

        <div>
            <input required="" name="password" type="password" value="" placeholder="Password">
            <label for="password">Password</label>
            @include('parts.errors', ['name' => 'password'])
        </div>
        <div>
            <input required="" name="password_confirmation" type="password" value=""
                placeholder="Password confirmation">
            <label for="password_confirmation">Confirm password</label>
            @include('parts.errors', ['name' => 'password_confirmation'])
        </div>

        @include('parts.terms')

        <div class="large">
            {!! Form::submit('Register', ['class' => 'btn oppose']) !!}
        </div>

        {!! Form::close() !!}

    </section>
    <section class="on_desktop">
        <img src="/img/login.svg">
    </section>
@endsection

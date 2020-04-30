@extends('layouts.main')

@section('content')
<p>
    You already have an account?
    <a class="ml-2 btn btn-primary btn-sm" href="{{ route('account.login') }}">Authenticate</a>
</p>

<hr />

<h2>Register a new account</h2>

{!! Form::open(['route' => 'account.store']) !!}

<p>Fill a username and an email address OR phone number, you will then be able to set a password to finish the registration process.</p>

<div class="form-group">
    {!! Form::label('username', 'Username') !!}
    <div class=" input-group mb-3">
        {!! Form::text('username', old('username'), ['class' => 'form-control', 'placeholder' => 'username', 'required']) !!}
        <div class="input-group-append">
            <span class="input-group-text" id="basic-addon2">{{ $domain }}</span>
        </div>
    </div>
</div>

<hr />
<div class="form-row">
    <div class="form-group col-md-6">
        {!! Form::label('email', 'New email') !!}
        {!! Form::email('email', old('email'), ['class' => 'form-control', 'placeholder' => 'username@server.com']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('email_confirmation', 'Email confirmation') !!}
        {!! Form::email('email_confirmation', old('email_confirm'), ['class' => 'form-control', 'placeholder' => 'username@server.com']) !!}
    </div>
</div>

<h2 class="text-center mb-0">OR</h2>

<div class="form-group">
    {!! Form::label('phone', 'Phone number') !!}
    {!! Form::text('phone', old('phone'), ['class' => 'form-control', 'placeholder' => '+123456789']) !!}
</div>

<div class="form-check mb-3">
    <a href="{{ route('account.terms') }}">Terms and Conditions</a><br />
    {!! Form::checkbox('terms', 'checked', false, ['class' => 'form-check-input', 'id' => 'terms']) !!}
    <label class="form-check-label" for="terms">I accept the Terms and Conditions</a></label>
</div>

<div class="form-group">
    {!! NoCaptcha::renderJs() !!}
    {!! NoCaptcha::display() !!}
</div>

{!! Form::submit('Register', ['class' => 'btn btn-primary']) !!}
{!! Form::close() !!}

@endsection
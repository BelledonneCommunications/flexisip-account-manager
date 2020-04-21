@extends('layouts.account')

@section('content')

<h2>Register a new account</h2>

{!! Form::open(['route' => 'account.store']) !!}

<p>Fill a username and an email address OR phone number, you will then be able to set a password to finish the registration process.</p>

<div class="form-group">
    {!! Form::label('username', 'Username') !!}
    {!! Form::text('username', old('username'), ['class' => 'form-control', 'placeholder' => 'username', 'required']) !!}
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

{!! Form::submit('Register', ['class' => 'btn btn-primary float-right']) !!}
{!! Form::close() !!}

@endsection
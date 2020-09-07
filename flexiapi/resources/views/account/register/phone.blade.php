@extends('layouts.main')

@section('content')

<h2>Register using a phone number</h2>

{!! Form::open(['route' => 'account.store.phone']) !!}

<p>Please enter your phone number and optionally a username. When the username is set, it will identify you on the service: other users won't need to know your phone number to call you.<br />The next step of the registration procedure will ask you to setup a password.</p>

<div class="form-group">
    {!! Form::label('phone', 'Phone number') !!}
    {!! Form::text('phone', old('phone'), ['class' => 'form-control', 'placeholder' => '+123456789']) !!}
</div>

<div class="form-group">
    {!! Form::label('username', 'SIP Username (optional)') !!}
    <div class=" input-group mb-3">
        {!! Form::text('username', old('username'), ['class' => 'form-control', 'placeholder' => 'username']) !!}
        <div class="input-group-append">
            <span class="input-group-text" id="basic-addon2">{{ $domain }}</span>
        </div>
    </div>
</div>

@include('parts.terms')

{!! Form::submit('Register', ['class' => 'btn btn-primary btn-centered']) !!}
{!! Form::close() !!}

@endsection
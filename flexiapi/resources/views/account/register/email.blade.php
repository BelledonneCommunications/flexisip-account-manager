@extends('layouts.main')

@section('content')

<h2>Register using an email address</h2>

{!! Form::open(['route' => 'account.store.email']) !!}

<p>Fill a username and an email address, you will then be able to set a password to finish the registration process.</p>

<div class="form-group">
    {!! Form::label('username', 'SIP Username') !!}
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
        {!! Form::label('email', 'Email') !!}
        {!! Form::email('email', old('email'), ['class' => 'form-control', 'placeholder' => 'bob@example.net']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('email_confirmation', 'Email confirmation') !!}
        {!! Form::email('email_confirmation', old('email_confirm'), ['class' => 'form-control', 'placeholder' => 'bob@example.net']) !!}
    </div>
</div>

@if (!empty(config('app.newsletter_registration_address')))
    <div class="form-check mb-3">
        {!! Form::checkbox('newsletter', 'true', false, ['class' => 'form-check-input', 'id' => 'newsletter']) !!}
        <label class="form-check-label" for="newsletter">I would like to subscribe to the newsletter</a></label>
    </div>
@endif

@include('parts.terms')

{!! Form::submit('Register', ['class' => 'btn btn-primary btn-centered']) !!}
{!! Form::close() !!}

@endsection
@extends('layouts.main', ['welcome' => true])

@section('content')

<section>

<p class="oppose">
    You already have an account?
    <a class="btn btn-secondary" href="{{ route('account.login') }}">Login</a>
</p>

<h1><i class="material-icons">account_circle</i> Register</h1>

@include('parts.tabs.register')

{!! Form::open(['route' => 'account.store']) !!}

<div>
    {!! Form::text('username', old('username'), ['placeholder' => 'username', 'required']) !!}
    {!! Form::label('username', 'Username') !!}
    @include('parts.errors', ['name' => 'username'])
</div>

<div>
    <input type="text" name="username" value="{{ $domain }}" disabled>
</div>

<div>
    {!! Form::email('email', old('email'), ['placeholder' => 'bob@example.net', 'required']) !!}
    {!! Form::label('email', 'Email') !!}
    @include('parts.errors', ['name' => 'email'])
</div>
<div>
    {!! Form::email('email_confirmation', old('email_confirm'), ['placeholder' => 'bob@example.net', 'required']) !!}
    {!! Form::label('email_confirmation', 'Confirm email') !!}
    @include('parts.errors', ['name' => 'email_confirmation'])
</div>

<div>
    {!! Form::password('password', ['required']) !!}
    {!! Form::label('password', 'Password') !!}
    @include('parts.errors', ['name' => 'password'])
</div>
<div>
    {!! Form::password('password_confirmation', ['required']) !!}
    {!! Form::label('password_confirmation', 'Confirm password') !!}
    @include('parts.errors', ['name' => 'password_confirmation'])
</div>

@if (!empty(config('app.newsletter_registration_address')))
    <div class="large checkbox">
        {!! Form::checkbox('newsletter', 'true', false, ['class' => 'form-check-input', 'id' => 'newsletter']) !!}
        <label class="form-check-label" for="newsletter">I would like to subscribe to the newsletter</a></label>
    </div>
@endif

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

@section('footer')
Hop
@endsection
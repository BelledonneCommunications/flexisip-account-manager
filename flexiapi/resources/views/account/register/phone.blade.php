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
    {!! Form::text('username', $domain, ['disabled']) !!}
</div>

<div>
    {!! Form::text('phone', old('phone'), ['placeholder' => '+123456789', 'required']) !!}
    {!! Form::label('phone', 'Phone number') !!}
    @include('parts.errors', ['name' => 'phone'])
</div>
<div></div>

<div>
    {!! Form::password('password', ['required']) !!}
    {!! Form::label('password', 'Password') !!}
    @include('parts.errors', ['name' => 'password'])
</div>
<div>
    {!! Form::password('password_confirmation', ['required']) !!}
    {!! Form::label('password_confirmation', 'Confirm password') !!}
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
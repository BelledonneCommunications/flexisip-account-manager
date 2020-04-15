@extends('layouts.account')

@section('content')

<h2>Change my account email address</h2>

@if (!empty($account->email))
    <p>Currently: {{ $account->email }}</p>
@else
    <p>No email yet</p>
@endif

{!! Form::open(['route' => 'account.email.update']) !!}
<div class="form-group">
    {!! Form::label('email', 'New email') !!}
    {!! Form::email('email', old('email'), ['class' => 'form-control', 'placeholder' => 'username@server.com', 'required']) !!}
</div>
<div class="form-group">
    {!! Form::label('email_confirmation', 'Email confirmation') !!}
    {!! Form::email('email_confirmation', old('email_confirm'), ['class' => 'form-control', 'placeholder' => 'username@server.com', 'required']) !!}
</div>

{!! Form::submit('Change', ['class' => 'btn btn-primary float-right']) !!}
{!! Form::close() !!}

@endsection
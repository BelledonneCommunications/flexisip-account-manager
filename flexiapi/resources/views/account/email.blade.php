@extends('layouts.main')

@section('content')

<h2>Change my account email address</h2>

@if (!empty($account->email))
    <p>Currently: {{ $account->email }}</p>
@else
    <p>No email yet</p>
@endif

{!! Form::open(['route' => 'account.email.request_update']) !!}
<div class="form-group">
    {!! Form::email('email', old('email'), ['class' => 'form-control', 'placeholder' => 'bob@example.net', 'required']) !!}
    {!! Form::label('email', 'New email') !!}
</div>
<div class="form-group">
    {!! Form::email('email_confirmation', old('email_confirm'), ['class' => 'form-control', 'placeholder' => 'bob@example.net', 'required']) !!}
    {!! Form::label('email_confirmation', 'Email confirmation') !!}
</div>

{!! Form::hidden('email_current', $account->email) !!}

{!! Form::submit('Change', ['class' => 'btn btn-primary btn-centered']) !!}
{!! Form::close() !!}

@endsection
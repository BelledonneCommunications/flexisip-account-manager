@extends('layouts.account')

@section('content')

@if ($account->passwords()->count() > 0)
    <h2>Change my account password</h2>
@else
    <h2>Set my account password</h2>
@endif

{!! Form::open(['route' => 'account.password.update']) !!}
@if ($account->passwords()->count() > 0)
    <div class="form-group">
        {!! Form::label('old_password', 'Old password') !!}
        {!! Form::password('old_password', ['class' => 'form-control', 'required']) !!}
    </div>
@endif
<div class="form-group">
    {!! Form::label('password', 'New password') !!}
    {!! Form::password('password', ['class' => 'form-control', 'required']) !!}
</div>
<div class="form-group">
    {!! Form::label('password_confirmation', 'Password confirmation') !!}
    {!! Form::password('password_confirmation', ['class' => 'form-control', 'required']) !!}
</div>
<div class="form-check">
    {!! Form::checkbox('password_sha256', 'checked', $account->passwords()->where('algorithm', 'SHA-256')->exists(), ['class' => 'form-check-input']) !!}
    {!! Form::label('password_sha256', 'Use a SHA-256 encrypted password. This stronger password might not work with some old SIP clients.', ['class' => 'form-check-label']) !!}
</div>

{!! Form::submit('Change', ['class' => 'btn btn-primary btn-centered']) !!}
{!! Form::close() !!}

@endsection
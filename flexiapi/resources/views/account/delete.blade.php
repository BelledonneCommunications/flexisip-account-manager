@extends('layouts.account')

@section('content')

<h2>Delete my account</h2>

{!! Form::open(['route' => 'account.destroy', 'method' => 'delete']) !!}

<p>You are going to permanently delete your account.</p>
<p>Please enter your complete username to confirm: <b>{{ $account->identifier }}</b>.</p>

<div class="form-group">
    {!! Form::label('identifier', 'Username') !!}
    {!! Form::text('identifier', old('identifier'), ['class' => 'form-control', 'placeholder' => 'bob@example.net', 'required']) !!}
</div>

{!! Form::hidden('identifier_confirm', $account->identifier) !!}

{!! Form::submit('Delete', ['class' => 'btn btn-danger btn-centered']) !!}
{!! Form::close() !!}

@endsection
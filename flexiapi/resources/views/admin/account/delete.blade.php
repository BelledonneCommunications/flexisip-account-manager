@extends('layouts.main')

<h2>Delete an account</h2>

{!! Form::open(['route' => 'admin.account.destroy', 'method' => 'delete']) !!}

<p>You are going to permanently delete the following account. Please confirm your action.</p>
<p><b>{{ $account->identifier }}</b></p>

{!! Form::hidden('account_id', $account->id) !!}

{!! Form::submit('Delete', ['class' => 'btn btn-danger btn-centered']) !!}
{!! Form::close() !!}

@endsection
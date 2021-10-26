@extends('layouts.account')

@section('breadcrumb')
<li class="breadcrumb-item" aria-current="page">
    <a href="{{ route('admin.account.index') }}">Accounts</a>
</li>
<li class="breadcrumb-item" aria-current="page">
    <a href="{{ route('admin.account.show', $action->account) }}">{{ $action->account->identifier }}</a>
</li>
<li class="breadcrumb-item active" aria-current="page">
    Actions
</li>
<li class="breadcrumb-item active" aria-current="page">Delete</li>
@endsection

@section('content')

<h2>Delete an account action</h2>

{!! Form::open(['route' => ['admin.account.action.destroy', $action->account, $action], 'method' => 'delete']) !!}

<p>You are going to permanently delete the following account action. Please confirm your action.</p>
<p><b>{{ $action->key }}</b></p>

{!! Form::hidden('account_id', $action->account->id) !!}
{!! Form::hidden('action_id', $action->id) !!}

{!! Form::submit('Delete', ['class' => 'btn btn-danger btn-centered']) !!}
{!! Form::close() !!}

@endsection
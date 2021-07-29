@extends('layouts.account')

@section('breadcrumb')
<li class="breadcrumb-item" aria-current="page">
    <a href="{{ route('admin.account.index') }}">Accounts</a>
</li>
<li class="breadcrumb-item" aria-current="page">
    <a href="{{ route('admin.account.show', $account->id) }}">{{ $account->identifier }}</a>
</li>
<li class="breadcrumb-item active" aria-current="page">Delete</li>
@endsection

@section('content')

<h2>Delete an account</h2>

{!! Form::open(['route' => 'admin.account.destroy', 'method' => 'delete']) !!}

<p>You are going to permanently delete the following account account. Please confirm your action.</p>
<p><b>{{ $account->identifier }}</b></p>

{!! Form::hidden('account_id', $account->id) !!}

{!! Form::submit('Delete', ['class' => 'btn btn-danger btn-centered']) !!}
{!! Form::close() !!}

@endsection
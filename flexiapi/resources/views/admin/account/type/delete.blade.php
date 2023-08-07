@extends('layouts.main')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('admin.account.index') }}">Accounts</a>
</li>
<li class="breadcrumb-item">
    <a href="{{ route('admin.account.type.index') }}">Types</a>
</li>
<li class="breadcrumb-item">
    {{ $type->key }}
</li>
<li class="breadcrumb-item active" aria-current="page">Delete</li>
@endsection

@section('content')

<h2>Delete an account type</h2>

{!! Form::open(['route' => ['admin.account.type.destroy', $type->id], 'method' => 'delete']) !!}

<p>You are going to permanently delete the following type. Please confirm your action.</p>
<p><b>{{ $type->key }}</b></p>

{!! Form::submit('Delete', ['class' => 'btn btn-danger btn-centered']) !!}
{!! Form::close() !!}

@endsection
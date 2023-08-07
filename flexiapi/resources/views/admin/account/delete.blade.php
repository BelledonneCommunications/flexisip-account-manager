@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.account.index') }}">Accounts</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Delete</li>
@endsection

@section('content')
    <h2>Delete an account</h2>

    {!! Form::open(['route' => 'admin.account.destroy', 'method' => 'delete']) !!}

    <div class="large">
        <p>You are going to permanently delete the following account. Please confirm your action.<br />
            <b>{{ $account->identifier }}</b>
        </p>

        {!! Form::hidden('account_id', $account->id) !!}
    </div>
    <div>
        {!! Form::submit('Delete', ['class' => 'btn']) !!}

    </div>

    {!! Form::close() !!}
@endsection

@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.account.edit', $action->account) }}">{{ $action->account->identifier }}</a>
    </li>
    <li class="breadcrumb-item active">
        Actions
    </li>
    <li class="breadcrumb-item active" aria-current="page">Delete</li>
@endsection

@section('content')
    <h2>Delete an account action</h2>

    <form method="POST" action="{{ route('admin.account.action.destroy', [$action->account, $action]) }}"
        accept-charset="UTF-8">
        @csrf
        @method('delete')

        <div>
            <p>You are going to permanently delete the following account action. Please confirm your action.</p>
            <p><b>{{ $action->key }}</b></p>
        </div>
        <input name="account_id" type="hidden" value="{{ $action->account->id }}">
        <input name="action_id" type="hidden" value="{{ $action->id }}">

        <div>
            <input class="btn" type="submit" value="Delete">
        </div>
    </form>
@endsection

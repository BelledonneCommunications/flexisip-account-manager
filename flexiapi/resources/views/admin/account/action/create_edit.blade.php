@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    @include('admin.account.parts.breadcrumb_accounts_edit', ['account' => $account])
    <li class="breadcrumb-item active" aria-current="page">
        Actions
    </li>
@endsection

@section('content')
    @if ($action->id)
        <h2>Edit an account action</h2>
    @else
        <h2>Create an account action</h2>
    @endif

    <form method="POST"
        action="{{ $action->id ? route('admin.account.action.update', [$action->account->id, $action->id]) : route('admin.account.action.store', $account->id) }}"
        accept-charset="UTF-8">
        @method($action->id ? 'put' : 'post')
        @csrf
        <div>
            <input type="text" name="key" value="{{ $action->key }}" placeholder="action_key">
            <label for="key">Key</label>
        </div>
        <div>
            <input type="text" name="code" value="{{ $action->code }}" placeholder="12ab45">
            <label for="code">Code</label>
        </div>
        <div>
            <input class="btn btn-success" type="submit" value="{{ $action->id ? 'Update' : 'Create' }}">
        </div>
    </form>
@endsection

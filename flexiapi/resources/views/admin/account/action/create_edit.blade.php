@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    @include('admin.account.parts.breadcrumb_accounts_show', ['account' => $account])
    <li class="breadcrumb-item active" aria-current="page">
        {{ __('Actions') }}
    </li>
@endsection

@section('content')
    @if ($action->id)
        <h2>{{ __('Edit') }}</h2>
    @else
        <h2>{{ __('Create') }}</h2>
    @endif

    <form method="POST"
        action="{{ $action->id ? route('admin.account.action.update', [$action->account->id, $action->id]) : route('admin.account.action.store', $account->id) }}"
        accept-charset="UTF-8">
        @method($action->id ? 'put' : 'post')
        @csrf
        <div>
            <input type="text" name="key" value="{{ $action->key }}" placeholder="action_key">
            <label for="key">{{ __('Key') }}</label>
        </div>
        <div>
            <input type="text" name="code" value="{{ $action->code }}" placeholder="12ab45">
            <label for="code">{{ __('Code') }}</label>
        </div>
        <div>
            <input class="btn btn-success" type="submit" value="{{ $action->id ? __('Update') : __('Create') }}">
        </div>
    </form>
@endsection

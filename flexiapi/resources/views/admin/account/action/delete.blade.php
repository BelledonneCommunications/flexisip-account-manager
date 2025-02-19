@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    @include('admin.account.parts.breadcrumb_accounts_edit', ['account' => $account])
    <li class="breadcrumb-item active">
        {{ __('Actions') }}
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Delete') }}</li>
@endsection

@section('content')
    <h2>{{ __('Delete') }}</h2>

    <form method="POST" action="{{ route('admin.account.action.destroy', [$action->account, $action]) }}"
        accept-charset="UTF-8">
        @csrf
        @method('delete')

        <div>
            <p>{{ __('You are going to permanently delete the following element. Please confirm your action.') }}</p>
            <p><b>{{ $action->key }}</b></p>
        </div>
        <input name="account_id" type="hidden" value="{{ $action->account->id }}">
        <input name="action_id" type="hidden" value="{{ $action->id }}">

        <div>
            <input class="btn" type="submit" value="{{ __('Delete') }}">
        </div>
    </form>
@endsection

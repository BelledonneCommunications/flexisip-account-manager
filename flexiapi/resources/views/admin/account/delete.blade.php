@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    @include('admin.account.parts.breadcrumb_accounts_show', ['account' => $account])
    <li class="breadcrumb-item active" aria-current="page">{{ __('Delete') }}</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph ph-trash"></i> {{ __('Delete') }}</h1>

        <a href="{{ route('admin.account.show', $account->id) }}" class="btn secondary oppose">{{ __('Cancel') }}</a>
        <input form="delete" class="btn" type="submit" value="{{ __('Delete') }}">
    </header>
    <form id="delete" method="POST" action="{{ route('admin.account.destroy') }}" accept-charset="UTF-8">
        @csrf
        @method('delete')

        <div class="large">
            <p>{{ __('You are going to permanently delete the following element. Please confirm your action.') }}<br />
                <b>{{ $account->identifier }}</b>
            </p>
            <input name="account_id" type="hidden" value="{{ $account->id }}">
        </div>
        <div>
        </div>
    </form>
@endsection

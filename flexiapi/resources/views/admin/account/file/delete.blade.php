@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.accounts.show', ['account' => $account])
    <li class="breadcrumb-item active">
        {{ __('Files') }}
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Delete') }}</li>
@endsection

@section('content')
    <h2>{{ __('Delete') }}</h2>

    <form method="POST" action="{{ route('admin.account.file.destroy', [$file->account, $file->id]) }}"
        accept-charset="UTF-8">
        @csrf
        @method('delete')

        <div>
            <p>{{ __('You are going to permanently delete the following element. Please confirm your action.') }}</p>
            <p><b>{{ $file->id }}</b></p>
        </div>
        <input name="account_id" type="hidden" value="{{ $file->account->id }}">
        <input name="action_id" type="hidden" value="{{ $file->id }}">

        <div>
            <input class="btn" type="submit" value="{{ __('Delete') }}">
        </div>
    </form>
@endsection

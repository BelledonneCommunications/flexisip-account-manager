@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.accounts.show', ['account' => $account])
    <li class="breadcrumb-item active">
        {{ __('CardDav credentials') }}
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Delete') }}</li>
@endsection

@section('content')
    <h1><i class="ph ph-trash"></i> {{ __('CardDav credentials') }}</h1>

    <form method="POST" action="{{ route('admin.account.carddavs.destroy', [$account, $carddavCredentials->cardDavServer]) }}"
        accept-charset="UTF-8">
        @csrf
        @method('delete')

        <div>
            <p>{{ __('You are going to permanently delete the following element. Please confirm your action.') }}</p>
            <p><b>{{ $carddavCredentials->identifier }}</b></p>
        </div>

        <input name="account_id" type="hidden" value="{{ $account->id }}">
        <input name="carddav_id" type="hidden" value="{{ $carddavCredentials->cardDavServer->id }}">

        <div>
            <input class="btn" type="submit" value="{{ __('Delete') }}">
        </div>
    </form>
@endsection

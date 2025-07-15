@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    @include('admin.account.parts.breadcrumb_accounts_show', ['account' => $account])
    <li class="breadcrumb-item active">
        {{ __('Contacts') }}
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Delete') }}</li>
@endsection

@section('content')
    <h2>{{ __('Delete') }}</h2>

    <div>
        <p>{{ __('You are going to permanently delete the following element. Please confirm your action.') }}</p>
        <p><b>{{ $contact->identifier }}</b></p>
    </div>

    <form method="POST" action="{{ route('admin.account.contact.destroy', [$account]) }}" accept-charset="UTF-8">
        @csrf
        @method('delete')

        <input name="account_id" type="hidden" value="{{ $account->id }}">
        <input name="contact_id" type="hidden" value="{{ $contact->id }}">
        <div>
            <input class="btn" type="submit" value="{{ __('Delete') }}">
        </div>
    </form>
@endsection

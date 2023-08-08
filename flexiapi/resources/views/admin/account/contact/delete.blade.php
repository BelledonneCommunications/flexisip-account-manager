@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item"'>
        <a href="{{ route('admin.account.index') }}">Accounts</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('admin.account.edit', $account) }}">{{ $account->identifier }}</a>
    </li>
    <li class="breadcrumb-item active">
        Contacts
    </li>
    <li class="breadcrumb-item active" aria-current="page">Delete</li>
@endsection

@section('content')
    <h2>Delete an account contact</h2>

    <form method="POST" action="{{ route('admin.account.contact.destroy', [$account]) }}" accept-charset="UTF-8">
        @csrf
        @method('delete')

        <div>
            <p>You are going to remove the following contact from the contact list. Please confirm your action.</p>
            <p><b>{{ $contact->identifier }}</b></p>
        </div>

        <input name="account_id" type="hidden" value="{{ $account->id }}">
        <input name="contact_id" type="hidden" value="{{ $contact->id }}">
        <div>
            <input class="btn" type="submit" value="Remove">
        </div>
    </form>
@endsection

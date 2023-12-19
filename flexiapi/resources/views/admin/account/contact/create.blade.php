@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    <li class="breadcrumb-item active" aria-current="page">Add Contact</li>
@endsection

@section('content')
    <header>
        <h1><i class="material-symbols-outlined">person_add</i> Add a Contact</h1>
        <a href="{{ route('admin.account.edit', $account->id) }}" class="btn btn-secondary oppose">Cancel</a>
        <input form="add_contact" class="btn" type="submit" value="Add">
    </header>
    <form id="add_contact" method="POST" action="{{ route('admin.account.contact.store', $account->id) }}" accept-charset="UTF-8">
        @csrf
        @method('post')
        <div>
            <input placeholder="username@server.com" name="sip" type="text" id="sip" required>
            <label for="sip">Local SIP Address</label>
            @include('parts.errors', ['name' => 'sip'])
        </div>
    </form>
@endsection

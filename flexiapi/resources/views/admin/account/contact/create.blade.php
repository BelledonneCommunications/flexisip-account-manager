@extends('layouts.main')

@section('content')
    <h2>Add a Contact to the Account</h2>

    <form method="POST" action="{{ route('admin.account.contact.store', $account->id) }}" accept-charset="UTF-8">
        @csrf
        @method('post')
        <div>
            <input placeholder="username@server.com" name="sip" type="text" id="sip">
            <label for="sip">SIP Address</label>
        </div>
        <div>
            <input class="btn" type="submit" value="Add">
        </div>
    </form>
@endsection

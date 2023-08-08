@extends('layouts.main')

@section('content')
    <h2>Delete my account</h2>

    <form method="POST" action="{{ route('account.destroy') }}" accept-charset="UTF-8">
@csrf

    @method('delete')

    <p>You are going to permanently delete your account.</p>
    <p>Please enter your complete username to confirm: <b>{{ $account->identifier }}</b>.</p>

    <div>
        <label for="identifier">Username</label>
        <input placeholder="bob@example.net" name="identifier" type="text" value="{{ old('identifier') }}">
    </div>

    <input name="identifier_confirm" type="hidden" value="{{ $account->identifier }}">

    <input class="btn" type="submit" value="Delete">
    </form>
@endsection

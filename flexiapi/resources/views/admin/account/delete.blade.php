@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.account.index') }}">Accounts</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Delete</li>
@endsection

@section('content')
    <h2>Delete an account</h2>

    <form method="POST" action="{{ route('admin.account.destroy') }}" accept-charset="UTF-8">
@csrf

    @method('delete')

    <div class="large">
        <p>You are going to permanently delete the following account. Please confirm your action.<br />
            <b>{{ $account->identifier }}</b>
        </p>

        <input name="account_id" type="hidden" value="{{ $account->id }}">
    </div>
    <div>
        <input class="btn" type="submit" value="Delete">

    </div>

    </form>
@endsection

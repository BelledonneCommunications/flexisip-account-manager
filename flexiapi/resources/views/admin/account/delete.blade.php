@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.account.index') }}">Accounts</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Delete</li>
@endsection

@section('content')
    <header>
        <h1><i class="material-icons-outlined">delete</i> Delete an account</h1>

        <a href="{{ route('admin.account.edit', $account->id) }}" class="btn btn-secondary oppose">Cancel</a>
        <input form="delete" class="btn" type="submit" value="Delete">
    </header>
    <form id="delete" method="POST" action="{{ route('admin.account.destroy') }}" accept-charset="UTF-8">
        @csrf
        @method('delete')

        <div class="large">
            <p>You are going to permanently delete the following account. Please confirm your action.<br />
                <b>{{ $account->identifier }}</b>
            </p>
            <input name="account_id" type="hidden" value="{{ $account->id }}">
        </div>
        <div>
        </div>
    </form>
@endsection

@extends('layouts.account')

@section('content')

<div class="list-group">
    <a href="{{ route('account.email') }}" class="list-group-item list-group-item-action">
        <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1">Change my current account email</h5>
        </div>
        @if (!empty($account->email))
            <p class="mb-1">{{ $account->email }}</p>
        @else
            <p class="mb-1">No email yet</p>
        @endif
    </a>
    <a href="{{ route('account.password') }}" class="list-group-item list-group-item-action">
        <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1">Change my password</h5>
        </div>
        @if ($account->passwords()->where('algorithm', 'SHA-256')->exists())
            <p class="mb-1">SHA-256 password configured</p>
        @else
            <p class="mb-1">MD5 password only</p>
        @endif
    </a>
    <a href="{{ route('account.delete') }}" class="list-group-item list-group-item-action">
        <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1">Delete my account</h5>
        </div>
        <p class="mb-1">Remove your account from our service</p>
    </a>
</div>

@endsection
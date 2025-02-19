@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    @include('admin.account.parts.breadcrumb_accounts_edit', ['account' => $account])
    <li class="breadcrumb-item active" aria-current="page">{{ __('Reset password') }}</li>
@endsection

@section('content')

<header>
    <h1><i class="ph">envelope</i> {{ __('Reset password') }}</h1>
</header>

<p>An email will be sent to <b>{{ $account->email }}</b> with a unique link allowing the user to reset its password.</p>

<p>This link will be available for {{ config('app.reset_password_email_token_expiration_minutes')/60 }} hours.</p>

<p>
    <a class="btn" href="{{ route('admin.account.reset_password_email.send', $account) }}">
        <i class="ph">paper-plane-right</i> {{ __('Send') }}
    </a>
</p>

@endsection
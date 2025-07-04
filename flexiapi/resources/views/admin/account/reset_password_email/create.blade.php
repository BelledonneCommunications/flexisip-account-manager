@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    @include('admin.account.parts.breadcrumb_accounts_show', ['account' => $account])
    <li class="breadcrumb-item active" aria-current="page">{{ __('Reset password') }}</li>
@endsection

@section('content')

<header>
    <h1><i class="ph ph-envelope"></i> {{ __('Reset password') }}</h1>
</header>

<p>{{ __('An email will be sent to :email with a unique link allowing the user to reset its password.', ['email' => $account->email]) }}</p>

@if (config('app.reset_password_email_token_expiration_minutes') > 0)
    <p>{{ __('This link will be available for :hours hours', ['hours' => config('app.reset_password_email_token_expiration_minutes')/60]) }}</p>
@endif

<p>
    <a class="btn oppose" href="{{ route('admin.account.reset_password_email.send', $account) }}">
        <i class="ph ph-paper-plane-right"></i> {{ __('Send') }}
    </a>
</p>

@endsection
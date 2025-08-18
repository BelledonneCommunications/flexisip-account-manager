@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    @include('admin.account.parts.breadcrumb_accounts_show', ['account' => $account])
    <li class="breadcrumb-item active" aria-current="page">{{ __('Provisioning') }}</li>
@endsection

@section('content')

<header>
    <h1><i class="ph ph-envelope"></i> {{ __('Provisioning') }}</h1>
</header>

<p>{{ __('An email will be sent to :email with a QR Code and provisioning link.', ['email' => $account->email]) }}</p>

@if (config('app.provisioning_token_expiration_minutes') > 0)
    <p>{{ __('This link will be available for :hours hours', ['hours' => config('app.provisioning_token_expiration_minutes')/60]) }}</p>
@endif

<p>
    <a class="btn oppose" href="{{ route('admin.account.provisioning_email.send', $account) }}">
        <i class="ph ph-paper-plane-right"></i> {{ __('Send') }}
    </a>
</p>

@endsection
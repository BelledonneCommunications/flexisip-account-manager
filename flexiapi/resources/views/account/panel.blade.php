@extends('layouts.account')

@section('content')

<h2>Manage your account</h2>

<div class="list-group mb-3 pt-2">
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
    @if (config('app.devices_management') == true)
        <a href="{{ route('account.device.index') }}" class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1">Manage my devices</h5>
            </div>
            <p class="mb-1">See and delete the devices linked to your account</p>
        </a>
    @endif
    <a href="{{ route('account.password') }}" class="list-group-item list-group-item-action">
        <div class="d-flex w-100 justify-content-between">
            @if ($account->passwords()->count() > 0)
                <h5 class="mb-1">Change my password</h5>
            @else
                <h5 class="mb-1">Set my password</h5>
            @endif
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

@if($account->isAdmin())
    <h3>Admin area</h3>
    <div class="list-group mb-3">
        <a href="{{ route('admin.account.index') }}" class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1">Accounts</h5>
            </div>
            <p class="mb-1">Manage the Flexisip accounts</p>
        </a>
    </div>

    <h5>API Key</h5>

    <p>As an administrator you can generate an API key and use it to request the different API endpoints, <a href="{{ route('api') }}">check the related API documentation</a> to know how to use that key.</p>

    {!! Form::open(['route' => 'admin.api_key.generate']) !!}
    <div class="form-row">
        <div class="col-8">
        <input readonly class="form-control" placeholder="No key yet, press Generate"
            @if ($account->apiKey)
                value="{{ $account->apiKey->key }}"
            @endif
        >
        </div>
        <div class="col-4">
            <button type="submit" class="btn btn-primary">Generate</button>
        </div>
    </div>
{!! Form::close() !!}
@endif

<h3 class="mt-3">Account information</h3>

<div class="list-group">
<b>SIP address:</b> sip:{{ $account->identifier }}<br />
<b>Username:</b> {{ $account->username }}<br />
<b>Domain:</b> {{ $account->domain }}<br />
<br />
@if (!empty(config('app.proxy_registrar_address')))
    <b>Proxy/registrar address: </b> sip:{{ config('app.proxy_registrar_address') }}<br />
@endif
@if (!empty(config('app.transport_protocol_text')))
    <b>Transport: </b> {{ config('app.transport_protocol_text') }} <br />
@endif
</div>

@endsection
@extends('layouts.main')

@section('content')
    <header>
        <h1><i class="material-icons">dashboard</i> Dashboard</h1>
    </header>

    <p>
        <i class="material-icons">email</i>
        @if (!empty($account->email))
            {{ $account->email }}
        @else
            No email yet
        @endif
        <a href="{{ route('account.email.change') }}">Change my current account email</a>
    </p>

    <p>
        <i class="material-icons">call</i>
        @if (!empty($account->phone))
            {{ $account->phone }}
        @else
            No phone yet
        @endif
        <a href="{{ route('account.phone.change') }}">Change my current account phone</a>
    </p>
    @if (config('app.devices_management') == true)
        <p>
            <i class="material-icons">laptop</i>
            <a href="{{ route('account.device.index') }}">Manage my devices</a>
        </p>
    @endif
    <p>
        <i class="material-icons">lock</i>
        <a href="{{ route('account.password') }}">
            @if ($account->passwords()->count() > 0)
                Change my password
            @else
                Set my password
            @endif
        </a>
    </p>
    <p>

    <p>
        <i class="material-icons">delete</i>
        <a href="{{ route('account.delete') }}">Delete my account</a>
    </p>

    <h2><i class="material-icons">person</i> Account information</h2>

    <p><i class="material-icons">alternate_email</i> SIP address: sip:{{ $account->identifier }}</p>
    <p><i class="material-icons">person</i> Username: {{ $account->username }}</p>
    <p><i class="material-icons">dns</i> Domain: {{ $account->domain }}</p>

    @if (!empty(config('app.proxy_registrar_address')))
        <p><i class="material-icons">lan</i> Proxy/registrar address: sip:{{ config('app.proxy_registrar_address') }}</p>
    @endif
    @if (!empty(config('app.transport_protocol_text')))
        <p><i class="material-icons">settings_ethernet</i> Transport: {{ config('app.transport_protocol_text') }} </p>
    @endif

    <!--<h3 class="mt-3">Automatic authentication</h3>

    <p>You can automatically authenticate another device on this panel by flashing the following QR Code.
    Once generated the QR Code stays valid for a few minutes.</p>

    @foreach ($account->authTokens()->valid()->get() as $authToken)
    <img src="{{ route('auth_tokens.qrcode', ['token' => $authToken->token]) }}">
    @endforeach

    {!! Form::open(['route' => 'account.auth_tokens.create']) !!}
        <button type="submit" class="btn btn-primary">Generate</button>
    {!! Form::close() !!}-->

    <h2><i class="material-icons">key</i>API Key</h2>

    <p>You can generate an API key and use it to request the different API endpoints, <a href="{{ route('api') }}">check
            the related API documentation</a> to know how to use that key.</p>

    {!! Form::open(['route' => 'account.api_key.generate']) !!}
    <div>
        <input readonly class="form-control" placeholder="No key yet, press Generate"
            @if ($account->apiKey) value="{{ $account->apiKey->key }}" @endif>
        <label>Key</label>
    </div>
    <div>
        <button type="submit" class="btn btn-primary">Generate</button>
    </div>
    {!! Form::close() !!}

    @include('parts.account_variables', ['account' => $account])
@endsection

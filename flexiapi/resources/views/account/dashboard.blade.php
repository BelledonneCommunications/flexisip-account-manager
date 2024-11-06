@extends('layouts.main', ['grid' => true])

@section('content')
    <header>
        <h1><i class="ph">gauge</i> Dashboard</h1>
    </header>

    <div class="card">
        <h3><i class="ph">hand-waving</i> Welcome back</h3>
        <p>
            <i class="ph">envelope</i>
            @if (!empty($account->email))
                {{ $account->email }}
            @else
                No email yet
            @endif
            <a href="{{ route('account.email.change') }}">Change my current account email</a>
        </p>

        @if (config('app.phone_authentication'))
            <p>
                <i class="ph">phone</i>
                @if (!empty($account->phone))
                    {{ $account->phone }}
                @else
                    No phone yet
                @endif
                <a href="{{ route('account.phone.change') }}">Change my current account phone</a>
            </p>
        @endif

        <p>
            <i class="ph">devices</i>
            <a href="{{ route('account.device.index') }}">
                Edit my devices
            </a>
        </p>
        <p>
            <i class="ph">lock</i>
            <a href="{{ route('account.password.show') }}">
                @if ($account->passwords()->count() > 0)
                    Change my password
                @else
                    Set my password
                @endif
            </a>
        </p>

        <p>
            <i class="ph">key</i>
            <a href="{{ route('account.api_key.show') }}">
                API Key Management
            </a>
        </p>

        <p>
            <i class="ph">trash</i>
            <a href="{{ route('account.delete') }}">Delete my account</a>
        </p>
    </div>

    <div class="card">
        <h3><i class="ph">person</i> Account information</h3>

        <p><i class="ph">envelope</i> SIP address: sip:{{ $account->identifier }}</p>
        <p><i class="ph">user</i> Username: {{ $account->username }}</p>
        <p><i class="ph">hard-drives</i> Domain: {{ $account->domain }}</p>

        @if (!empty(config('app.proxy_registrar_address')))
            <p><i class="ph">lan</i> Proxy/registrar address: sip:{{ config('app.proxy_registrar_address') }}
            </p>
        @endif
        @if (!empty(config('app.transport_protocol_text')))
            <p><i class="ph">sliders</i> Transport: {{ config('app.transport_protocol_text') }} </p>
        @endif

        <!--<h3 class="mt-3">Automatic authentication</h3>

            <p>You can automatically authenticate another device on this panel by flashing the following QR Code.
            Once generated the QR Code stays valid for a few minutes.</p>

            @foreach ($account->authTokens()->valid()->get() as $authToken)
            <img src="{{ route('auth_tokens.qrcode', ['token' => $authToken->token]) }}">
            @endforeach

            <form method="POST" action="{{ route('account.auth_tokens.create') }}" accept-charset="UTF-8">
@csrf

                <button type="submit" class="btn btn-primary">Generate</button>
            </form>-->

    </div>

    @include('parts.account_variables', ['account' => $account])
@endsection

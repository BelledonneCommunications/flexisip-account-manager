@extends('layouts.main', ['grid' => true])

@section('content')
    <header>
        <h1><i class="ph ph-gauge"></i> {{ __('My Account') }}</h1>
    </header>

    <div class="card">
        <h3><i class="ph ph-hand-waving"></i> {{ __('Welcome on :app_name' , ['app_name' => space()->name]) }} </h3>
        <p>
            <i class="ph ph-envelope"></i>
            @if (!empty($account->email))
                {{ $account->email }}
            @else
                {{ __('No email yet') }}
            @endif
            <a href="{{ route('account.email.change') }}">{{ __('Edit') }}</a>
        </p>

        @if (space()->phone_registration)
            <p>
                <i class="ph ph-phone"></i>
                @if (!empty($account->phone))
                    {{ $account->phone }}
                @else
                    {{ __('No phone yet') }}
                @endif
                <a href="{{ route('account.phone.change') }}">{{ __('Edit') }}</a>
            </p>
        @endif

        <p>
            <i class="ph ph-devices"></i>
            {{ __('Devices') }}
            <a href="{{ route('account.device.index') }}">
                {{ __('Manage') }}
            </a>
        </p>
        <p>
            <i class="ph ph-lock"></i>
            {{ __('Password') }}
            <a href="{{ route('account.password.show') }}">
                @if ($account->passwords()->count() > 0)
                    {{ __('Edit') }}
                @else
                    {{ __('Create') }}
                @endif
            </a>
        </p>

        <p>
            <i class="ph ph-key"></i>
            {{ __('API Key') }}
            <a href="{{ route('account.api_keys.show') }}">
                {{ __('Manage') }}
            </a>
        </p>

        <p>
            <i class="ph ph-trash"></i>
            {{ __('My Account') }}
            <a href="{{ route('account.delete') }}">
                {{ __('Delete') }}
            </a>
        </p>
    </div>

    <div class="card">
        <h3><i class="ph ph-person"></i> {{ __('Information') }}</h3>

        <p><i class="ph ph-envelope"></i> {{ __('SIP Adress') }}: sip:{{ $account->identifier }}</p>
        <p><i class="ph ph-user"></i> {{ __('Username') }}: {{ $account->username }}</p>
        <p><i class="ph ph-globe-hemisphere-west"></i> {{ __('Domain') }}: {{ $account->domain }}</p>

        @if (!empty(space()?->account_proxy_registrar_address))
            <p><i class="ph ph-hard-drive"></i> Proxy/registrar address: sip:{{ space()?->account_proxy_registrar_address }}
            </p>
        @endif
        @if (!empty(config('app.transport_protocol_text')))
            <p><i class="ph ph-sliders"></i> {{ __('Transport') }}: {{ config('app.transport_protocol_text') }} </p>
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

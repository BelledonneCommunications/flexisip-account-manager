@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.spaces.integration')
    <li class="breadcrumb-item active" aria-current="page">{{ __('OpenID Connect configuration') }}</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph ph-key"></i> {{ $space->name }}</h1>
        @if ($space->oidcAuthenticationConfiguration)
            <a class="btn secondary oppose" title="{{ __('Delete') }}"
            href="{{ route('admin.spaces.oidc.delete', $space->domain) }}">
                <i class="ph ph-trash"></i>
            </a>
        @endif
    </header>

    @if ($space->unique_email)
        @if($accountWithoutEmail > 0)
            <div class="card warning large">
                <ul>
                    <li>
                        <span class="icon">
                            <i class="ph ph-warning"></i>
                        </span>
                        <div class="content">
                            <p>{{ __('Accounts Missing Email Address') }}</p>
                            <p>{{ __(":number accounts in this space don't have an email address set. Once OIDC is enabled, these users won't be able to log in, since authentication is based on matching email addresses.", ['number' => $accountWithoutEmail]) }}</p>
                        </div>
                    </li>
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.spaces.oidc.store', $space->domain) }}" id="show" accept-charset="UTF-8">
        @csrf
        @method('post')
        <div>
            <input placeholder="https://keycloak.server.tld/" required="required" name="authorization_server" type="url"
                value="{{ ($space->oidcAuthenticationConfiguration?->authorization_server) ?: old('authorization_server') }}">
            <label for="authorization_server">{{ __('Authorization server address') }}</label>
            @include('parts.errors', ['name' => 'authorization_server'])
            <span class="supporting">{{ __("The Issuer URL of your OpenID Connect provider, corresponding to the value of the `iss` field in the tokens.") }}</span>
        </div>
        <div>
            <input placeholder="sip_identity" name="sip_id_claim" type="text" required="required"
                value="{{ ($space->oidcAuthenticationConfiguration?->sip_id_claim) ?: old('sip_id_claim') ?: 'sip_identity' }}">
            <label for="sip_id_claim">{{ __('SIP identity claim') }}</label>
            @include('parts.errors', ['name' => 'sip_id_claim'])
            <span class="supporting">{{ __("The name of the claim containing the user’s SIP address. Defaults to `sip_identity`. If this is not available, the email address is used to identify the user.") }}</span>
        </div>
        <div>
            <input placeholder="flexiapi" required="required" name="audience" type="text"
                value="{{ ($space->oidcAuthenticationConfiguration?->audience) ?: old('audience') }}">
            <label for="audience">{{ __('Expected audience') }}</label>
            @include('parts.errors', ['name' => 'audience'])
            <span class="supporting">{{ __("The expected audience (aud) in the tokens. A token intended for another service will be rejected.") }}</span>
        </div>

        <h3 class="large">{{ __('Platform dedicated authentication') }}</h3>

        <p class="large">{{ __('To log in to the web panel using your OpenID Connect account, a client dedicated to the web panel must be configured with your provider.') }}</p>

        <div>
            <input placeholder="client_id" name="client_id" type="text" required="required"
                value="{{ ($space->oidcAuthenticationConfiguration?->client_id) ?: old('client_id') }}">
            <label for="client_id">{{ __('Client id') }}</label>
            @include('parts.errors', ['name' => 'client_id'])
        </div>
        <div>
            <input placeholder="client_secret" name="client_secret" type="text" value="{{ ($space->oidcAuthenticationConfiguration?->client_secret) ?: old('client_secret') }}">
            <label for="client_secret">{{ __('Client secret') }}</label>
            @include('parts.errors', ['name' => 'client_secret'])
        </div>

        <p class="large">{{ __('URI de redirection à déclarer dans ce client :') }} <code>{{ route('account.login.oidc.redirect') }}</code></p>
    </form>

    <br />

    <hr />

    @include('parts.errors', ['name' => 'refresh_keys_errors'])

    <input form="show" class="btn" type="submit"
        value="@if ($space->id) {{ __('Update') }}@else{{ __('Create') }} @endif">
    @else
        <div class="card danger large">
            <ul>
                <li>
                    <span class="icon">
                        <i class="ph ph-warning"></i>
                    </span>
                    <div class="content">
                        <p>{{ __('Cannot Enable OIDC Email Uniqueness Required') }}</p>
                        <p>{{ __("Email uniqueness is disabled. OIDC authentication cannot be enabled without this option. Please contact your super-admin.") }}</p>
                    </div>
                </li>
            </ul>
        </div>
    @endif


@endsection

@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.spaces.integration')
    <li class="breadcrumb-item active" aria-current="page">{{ __('SSO Server') }}</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph ph-key"></i> {{ $space->name }}</h1>
        @if ($space->ssoServer)
            <a class="btn secondary oppose" title="{{ __('Delete') }}"
            href="{{ route('admin.spaces.sso.delete', $space) }}">
            <i class="ph ph-trash"></i>
            </a>
        @endif
    </header>

    @if ($space->unique_email)

        @if($accountWithoutEmail > 0)
            <div class="panel panel-warning">
                <i class="ph ph-warning"></i>
                <div class="text">
                    <span class="title">{{ __('Accounts Missing Email Address') }}</span>
                    <span class="description"><strong>{{ $accountWithoutEmail }}</strong> {{ __("accounts in this space don't have an email address set. Once SSO is enabled, these users won't be able to log in, since authentication is based on matching email addresses.") }}</span>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.spaces.sso.store', $space) }}" id="show" accept-charset="UTF-8">
        @csrf
        @method('post')
        <div>
            <input placeholder="https://keycloak.server.tld/" required="required" name="server_url" type="url"
                value="{{ ($space->ssoServer?->server_url) ?: old('server_url') }}">
            <label for="server_url">{{ __('Server URL') }}</label>
            @include('parts.errors', ['name' => 'server_url'])
        </div>
        <div>
            <input placeholder="flexiapi" required="required" name="realm" type="text"
                value="{{ ($space->ssoServer?->realm) ?: old('realm') }}">
            <label for="realm">{{ __('Realm') }}</label>
            @include('parts.errors', ['name' => 'realm'])
        </div>
        <div>
            <input placeholder="sip_identity" name="sip_identifier" type="text" required="required"
                value="{{ ($space->ssoServer?->sip_identifier) ?: old('sip_identifier') }}">
            <label for="sip_identifier">{{ __('SIP Identifier') }}</label>
            @include('parts.errors', ['name' => 'sip_identifier'])
            <span class="supporting">{{ __("JWT key containing the user's SIP identity. sip_identity by default.") }}</span>
        </div>
        <div>
            <input placeholder="client_id" name="client_id" type="text" required="required"
                value="{{ ($space->ssoServer?->client_id) ?: old('client_id') }}">
            <label for="client_id">{{ __('Client id') }}</label>
            @include('parts.errors', ['name' => 'client_id'])
        </div>
        <div>
            <input placeholder="client_secret" name="client_secret" type="text" value="{{ ($space->ssoServer?->client_secret) ?: old('client_secret') }}" required="required">
            <label for="client_secret">{{ __('Client Secret') }}</label>
            @include('parts.errors', ['name' => 'client_secret'])
        </div>
        <br>
        <div>
            <br>
            @include('parts.form.toggle', [
                'object' => $space->ssoServer ?? (object)['auto_provisioning' => false],
                'key' => 'auto_provisioning',
                'label' => __('Automatic user provisioning'),
                'tooltiptext'=> __("Automatic user provisioning allows Keycloak users with the required role to be created automatically if they don't already exist. Their SIP username will be generated from their email; if it already exists, a number will be appended to make it unique."),
                'attributes' => [
                    'class' => 'form-dependency',
                    'data-target' => '#role_provisioning',
                ],
            ])
        </div>
        <div>
            <input placeholder="linphone" name="role_provisioning" type="text" id="role_provisioning" value="{{ ($space->ssoServer?->role_provisioning) ?: old('role_provisioning') }}">
            <label for="role_provisioning">{{ __('Role') }}</label>
            @include('parts.errors', ['name' => 'role_provisioning'])
        </div>
    </form>

    <br />

    <hr />

    @include('parts.errors', ['name' => 'public_key'])

    <br />

    @if ($space->ssoServer?->public_key)
        <h4>{{ __('Public key') }}</h4> <small>{{ __('Last update') }}: {{ $space->ssoServer->updated_at }}</small>

        <br />
        <pre style="display: inline-block;"><code>{{ $space->ssoServer->public_key }}</code></pre>
        <br />
        <a class="btn small secondary"
            href="{{ route('admin.spaces.sso.refresh_public_key', $space) }}">{{ __('Refresh') }}</a>
        <hr />
    @endif


    <input form="show" class="btn" type="submit"
        value="@if ($space->id) {{ __('Update') }}@else{{ __('Create') }} @endif">
    @else
        <div class="panel panel-danger">
            <i class="ph ph-warning"></i>
            <div class="text">
                <span class="title">{{ __('Cannot Enable SSO — Email Uniqueness Required') }}</span>
                <span class="description">{{ __("Email uniqueness is disabled. SSO authentication cannot be enabled without this option. Please contact your super-admin.") }}</span>
            </div>
        </div>
    @endif


@endsection

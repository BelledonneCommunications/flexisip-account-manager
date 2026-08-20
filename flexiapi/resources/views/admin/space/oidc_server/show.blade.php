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
            <input placeholder="https://keycloak.server.tld/" required="required" name="server_url" type="url"
                value="{{ ($space->oidcAuthenticationConfiguration?->server_url) ?: old('server_url') }}">
            <label for="server_url">{{ __('Server URL') }}</label>
            @include('parts.errors', ['name' => 'server_url'])
        </div>
        <div>
            <input placeholder="flexiapi" required="required" name="realm" type="text"
                value="{{ ($space->oidcAuthenticationConfiguration?->realm) ?: old('realm') }}">
            <label for="realm">{{ __('Realm') }}</label>
            @include('parts.errors', ['name' => 'realm'])
        </div>
        <div>
            <input placeholder="sip_identity" name="sip_identifier" type="text" required="required"
                value="{{ ($space->oidcAuthenticationConfiguration?->sip_identifier) ?: old('sip_identifier') }}">
            <label for="sip_identifier">{{ __('SIP Identifier') }}</label>
            @include('parts.errors', ['name' => 'sip_identifier'])
            <span class="supporting">{{ __("JWT key containing the user's SIP identity. sip_identity by default.") }}</span>
        </div>

        <h3 class="large">{{ __('Platform dedicated authentication') }}</h3>

        <div class="card info large">
            <ul>
                <li>
                    <span class="icon">
                        <i class="ph ph-info"></i>
                    </span>
                    <div class="content">
                        <p>{{ __('The platform is offering direct OIDC authentication as well.') }}</p>
                        <p>{{ __('If you want to enable this feature please create a dedicated client in your OIDC provider panel and fill the fields bellow.') }}</p>
                    </div>
                </li>
            </ul>
        </div>

        <div>
            <input placeholder="client_id" name="client_id" type="text"
                value="{{ ($space->oidcAuthenticationConfiguration?->client_id) ?: old('client_id') }}">
            <label for="client_id">{{ __('Client id') }}</label>
            @include('parts.errors', ['name' => 'client_id'])
        </div>
        <div>
            <input placeholder="client_secret" name="client_secret" type="text" value="{{ ($space->oidcAuthenticationConfiguration?->client_secret) ?: old('client_secret') }}">
            <label for="client_secret">{{ __('Client Secret') }}</label>
            @include('parts.errors', ['name' => 'client_secret'])
        </div>
        <br>

        <h3 class="large">{{ __('Automatic user provisioning') }}</h3>

        <div>
            <br>
            @include('parts.form.toggle', [
                'object' => $space->oidcAuthenticationConfiguration ?? (object)['auto_provisioning' => false],
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
            <input placeholder="linphone" name="role_provisioning" type="text" id="role_provisioning" value="{{ ($space->oidcAuthenticationConfiguration?->role_provisioning) ?: old('role_provisioning') }}">
            <label for="role_provisioning">{{ __('Role') }}</label>
            @include('parts.errors', ['name' => 'role_provisioning'])
        </div>
    </form>

    <br />

    <hr />

    @include('parts.errors', ['name' => 'public_key'])

    <br />

    @if ($space->oidcAuthenticationConfiguration?->public_key)
        <h4>{{ __('Public key') }}</h4> <small>{{ __('Last update') }}: {{ $space->oidcAuthenticationConfiguration->updated_at }}</small>

        <br />
        <pre style="display: inline-block;"><code>{{ $space->oidcAuthenticationConfiguration->public_key }}</code></pre>
        <br />
        <a class="btn small secondary"
            href="{{ route('admin.spaces.oidc.refresh_public_key', $space->domain) }}">{{ __('Refresh') }}</a>
        <hr />
    @endif


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

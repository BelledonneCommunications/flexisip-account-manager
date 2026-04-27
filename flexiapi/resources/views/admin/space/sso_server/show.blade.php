@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.spaces.integration')
    <li class="breadcrumb-item active" aria-current="page">{{ __('SSO Server') }}</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph ph-key"></i> {{ $space->name }}</h1>
    </header>

    <form method="POST" action="{{ route('admin.spaces.sso.store', $space->id) }}" id="show" accept-charset="UTF-8">
        @csrf
        @method('post')
        <div>
            <input placeholder="https://keycloak.server.tld/" required="required" name="sso_server_url" type="url"
                value="@if ($space->id) {{ $space->sso_server_url }}@else{{ old('sso_server_url') }} @endif">
            <label for="sso_server_url">{{ __('Server URL') }}</label>
            @include('parts.errors', ['name' => 'sso_server_url'])
        </div>
        <div>
            <input placeholder="cogip" required="required" name="sso_realm" type="text"
                value="@if ($space->id) {{ $space->sso_realm }}@else{{ old('sso_realm') }} @endif">
            <label for="sso_realm">{{ __('Realm') }}</label>
            @include('parts.errors', ['name' => 'sso_realm'])
        </div>
        <div>
            <input placeholder="sip_identity" name="sso_sip_identifier" type="text" required="required"
                value="@if ($space->id && isset($space->sso_sip_identifier)) {{ $space->sso_sip_identifier }}@else{{ old('sso_sip_identifier') }} @endif">
            <label for="sso_sip_identifier">{{ __('SIP Identifier') }}</label>
            @include('parts.errors', ['name' => 'sso_sip_identifier'])
            <span class="supporting">{{ __("JWT key containing the user's SIP identity. sip_identity by default.") }}/span>
        </div>
        <div>
            <input placeholder="client_id" name="sso_client_id" type="text" required="required"
                value="@if ($space->id && isset($space->sso_client_id)) {{ $space->sso_client_id }}@else{{ old('sso_client_id') }} @endif">
            <label for="sso_client_id">{{ __('Client id') }}</label>
            @include('parts.errors', ['name' => 'sso_client_id'])
        </div>
        <div>
            <input placeholder="client_secret" name="sso_client_secret" type="text" required="required"
                value="@if ($space->id && isset($space->sso_client_secret)) {{ $space->sso_client_secret }}@else{{ old('sso_client_secret') }} @endif">
            <label for="sso_client_secret">{{ __('Client Secret') }}</label>
            @include('parts.errors', ['name' => 'sso_client_secret'])
        </div>
        <br>
        <div>
            @include('parts.form.toggle', [
                'object' => $space,
                'key' => 'sso_auto_prov',
                'label' => __('Automatic user provisioning'),
                'attributes' => [
                    'class' => 'form-dependency',
                    'data-target' => '#sso_role_prov',
                ],
            ])
            <span
                class="supporting">{{ __('Enable automatic user provisioning: new users with the required Keycloak role will be registered automatically on their first sign-in.') }}</span>
        </div>
        <div>
            <input placeholder="sso_role_prov" name="sso_role_prov" type="text" required="required" id="sso_role_prov"
                value="@if ($space->id && isset($space->sso_role_prov)) {{ $space->sso_role_prov }}@else{{ old('sso_role_prov') }} @endif">
            <label for="sso_client_secret">{{ __('Role') }}</label>
            @include('parts.errors', ['name' => 'sso_role_prov'])
        </div>
    </form>

    <br />

    <hr />

    @include('parts.errors', ['name' => 'sso_public_key'])

    @if ($space->sso_public_key)
        <h4>{{ __('Public key') }}</h4> <small>{{ __('Last update') }}: {{ $space->updated_at }}</small>

        <br />
        <pre style="display: inline-block;"><code>{{ $space->sso_public_key }}</code></pre>
        <br />
        <a class="btn small secondary"
            href="{{ route('admin.spaces.sso.refresh_public_key', $space) }}">{{ __('Refresh') }}</a>
        <hr />
    @endif


    <input form="show" class="btn" type="submit"
        value="@if ($space->id) {{ __('Update') }}@else{{ __('Create') }} @endif">
@endsection

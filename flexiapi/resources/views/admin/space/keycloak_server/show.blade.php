@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.spaces.integration')
    <li class="breadcrumb-item active" aria-current="page">{{ __('Keycloak Server') }}</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph ph-key"></i> {{ $space->name }}</h1>
    </header>

    <form method="POST"
        action="{{ route('admin.spaces.keycloak.store', $space->id) }}"
        id="show" accept-charset="UTF-8">
        @csrf
        @method('post')
        <div>
            <input placeholder="https://keycloak.server.tld/" required="required" name="keycloak_server_url" type="url"
                value="@if($space->id){{ $space->keycloak_server_url }}@else{{ old('keycloak_server_url') }}@endif">
            <label for="keycloak_server_url">{{ __('Server URL') }}</label>
            @include('parts.errors', ['name' => 'keycloak_server_url'])
        </div>
        <div>
            <input placeholder="cogip" required="required" name="keycloak_realm" type="text"
                value="@if($space->id){{ $space->keycloak_realm }}@else{{ old('keycloak_realm') }}@endif">
            <label for="keycloak_realm">{{ __('Realm') }}</label>
            @include('parts.errors', ['name' => 'keycloak_realm'])
        </div>
        <div>
            <input placeholder="sip_identity" name="keycloak_sip_identifier" type="text" required="required"
                value="@if($space->id && isset($space->keycloak_sip_identifier)){{ $space->keycloak_sip_identifier }}@else{{ old('keycloak_sip_identifier') }}@endif">
            <label for="keycloak_sip_identifier">{{ __('SIP Identifier') }}</label>
            @include('parts.errors', ['name' => 'keycloak_sip_identifier'])
        </div>
    </form>

    <br />

    <hr />

    @include('parts.errors', ['name' => 'keycloak_public_key'])

    @if ($space->keycloak_public_key)
    <h4>{{ __('Public key') }}</h4> <small>{{ __('Last update') }}: {{ $space->updated_at }}</small>

    <br />
    <pre style="display: inline-block;"><code>{{ $space->keycloak_public_key }}</code></pre>
    <br />
    <a class="btn small secondary" href="{{ route('admin.spaces.keycloak.refresh_public_key', $space) }}">{{ __('Refresh') }}</a>
    <hr />
    @endif


    <input form="show" class="btn" type="submit" value="@if($space->id){{ __('Update') }}@else{{ __('Create') }}@endif">
@endsection

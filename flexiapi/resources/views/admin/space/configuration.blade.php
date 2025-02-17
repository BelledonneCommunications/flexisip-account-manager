@extends('layouts.main')

@section('breadcrumb')
    @if (auth()->user()->superAdmin)
        <li class="breadcrumb-item">
            <a href="{{ route('admin.spaces.index') }}">Spaces</a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('admin.spaces.show', $space->id) }}">
                {{ $space->host }}
            </a>
        </li>
    @else
        <li class="breadcrumb-item">
            <a href="{{ route('admin.spaces.me') }}">
                {{ $space->host }}
            </a>
        </li>
    @endif
    <li class="breadcrumb-item active" aria-current="page">Space Configuration</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph">globe-hemisphere-west</i> {{ $space->host }}</h1>
    </header>

    @include('admin.space.tabs')

    <form method="POST"
        action="{{ route('admin.spaces.configuration.update', $space) }}"
        accept-charset="UTF-8">
        @csrf
        @method('put')

        <div class="large">
            <textarea name="copyright_text" id="copyright_text">{{ $space->copyright_text }}</textarea>
            <label for="copyright_text">Copyright text</label>
            @include('parts.errors', ['name' => 'copyright_text'])
        </div>

        <div class="large">
            <textarea name="intro_registration_text" id="intro_registration_text">{{ $space->intro_registration_text }}</textarea>
            <label for="intro_registration_text">Registration introduction</label>
            <span class="supporting">Markdown text</span>
            @include('parts.errors', ['name' => 'intro_registration_text'])
        </div>

        <div class="large">
            <textarea name="confirmed_registration_text" id="confirmed_registration_text">{{ $space->confirmed_registration_text }}</textarea>
            <label for="confirmed_registration_text">Confirmed registration text</label>
            <span class="supporting">Markdown text</span>
            @include('parts.errors', ['name' => 'confirmed_registration_text'])
        </div>

        <div>
            <input name="newsletter_registration_address" id="newsletter_registration_address" placeholder="email@server.tld" type="email" value="{{ $space->newsletter_registration_address }}">
            <label for="newsletter_registration_address">Newsletter registration email address</label>
            <span class="supporting">An email will be sent to this address when someone register and join the newsletter</span>
            @include('parts.errors', ['name' => 'newsletter_registration_address'])
        </div>

        <div>
            <input name="account_proxy_registrar_address" id="account_proxy_registrar_address" placeholder="server.tld" value="{{ $space->account_proxy_registrar_address }}">
            <label for="account_proxy_registrar_address">Account proxy registrar address</label>
            <span class="supporting">Will be used for informational purpose in the user panel and communication emails</span>
            @include('parts.errors', ['name' => 'account_proxy_registrar_address'])
        </div>

        <h3 class="large">Provisioning</h3>

        <div class="large">
            <textarea style="min-height: 200px;" name="custom_provisioning_entries" id="custom_provisioning_entries">{{ $space->custom_provisioning_entries }}</textarea>
            <label for="custom_provisioning_entries">Custom entries</label>
            <span class="supporting">In ini format, will complete the other settings</span>
            @include('parts.errors', ['name' => 'custom_provisioning_entries'])
        </div>

        <div>
            @include('parts.form.toggle', ['object' => $space, 'key' => 'custom_provisioning_overwrite_all', 'label' => 'Allow client settings to be overwritten by the provisioning ones'])
        </div>

        <div>
            @include('parts.form.toggle', ['object' => $space, 'key' => 'provisioning_use_linphone_provisioning_header', 'label' => 'Enforce X-Linphone-Provisioning header'])
        </div>

        <h3 class="large">Space features</h3>
        <div>
            @include('parts.form.toggle', ['object' => $space, 'key' => 'public_registration', 'label' => 'Allow public registration'])
        </div>
        <div
            @include('parts.form.toggle', ['object' => $space, 'key' => 'phone_registration', 'label' => 'Allow registration using phones'])
        </div>
        <div>
            @include('parts.form.toggle', ['object' => $space, 'key' => 'intercom_features', 'label' => 'Enable intercom features'])
        </div>

        <div class="large">
            <input class="btn" type="submit" value="Update">
        </div>
    </form>
@endsection

@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.sip_domains.index') }}">SIP Domains</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
    <header>
        @if ($sip_domain->id)
            <h1><i class="material-symbols-outlined">hard-drives</i> {{ $sip_domain->domain }}</h1>
            <a href="{{ route('admin.sip_domains.index') }}" class="btn btn-secondary oppose">Cancel</a>
            <a class="btn btn-secondary" href="{{ route('admin.sip_domains.delete', $sip_domain->id) }}">
                <i class="ph">trash</i>
                Delete
            </a>
            <input form="create_edit_sip_domains" class="btn" type="submit" value="Update">
        @else
            <h1><i class="ph">user-rectangle</i> Create a SIP Domain</h1>
            <a href="{{ route('admin.sip_domains.index') }}" class="btn btn-secondary oppose">Cancel</a>
            <input form="create_edit_sip_domains" class="btn" type="submit" value="Create">
        @endif
    </header>

    <form method="POST" id="create_edit_sip_domains"
        action="{{ $sip_domain->id ? route('admin.sip_domains.update', $sip_domain->id) : route('admin.sip_domains.store') }}"
        accept-charset="UTF-8">
        @csrf
        @method($sip_domain->id ? 'put' : 'post')
        @if (!$sip_domain->id)
            <div>
                <input placeholder="Name" required="required" name="domain" type="text"
                    value="{{ $sip_domain->domain ?? old('domain') }}">
                <label for="username">Domain</label>
                @include('parts.errors', ['name' => 'domain'])
            </div>
        @endif

        @include('parts.form.toggle', ['object' => $sip_domain, 'key' => 'super', 'label' => 'Super domain'])

        <h3 class="large">Features</h3>

        @include('parts.form.toggle', ['object' => $sip_domain, 'key' => 'disable_chat_feature', 'label' => 'Chat feature', 'reverse' => true])
        @include('parts.form.toggle', ['object' => $sip_domain, 'key' => 'disable_meetings_feature', 'label' => 'Meeting feature', 'reverse' => true])
        @include('parts.form.toggle', ['object' => $sip_domain, 'key' => 'disable_broadcast_feature', 'label' => 'Conference feature', 'reverse' => true])
        @include('parts.form.toggle', ['object' => $sip_domain, 'key' => 'hide_settings', 'label' => 'General settings', 'reverse' => true])
        @include('parts.form.toggle', ['object' => $sip_domain, 'key' => 'hide_account_settings', 'label' => 'Account settings', 'reverse' => true])
        @include('parts.form.toggle', ['object' => $sip_domain, 'key' => 'disable_call_recordings_feature', 'label' => 'Record audio/video calls', 'reverse' => true])

        <h3 class="large">General toggles</h3>

        @include('parts.form.toggle', ['object' => $sip_domain, 'key' => 'only_display_sip_uri_username', 'label' => 'Only display usernames (hide SIP addresses)'])

        <div class="select">
            <select name="max_account">
                @foreach ([0 => 'No limit', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five'] as $key => $value)
                    <option value="{{ $key }}" @if ($sip_domain->max_account == $key) selected="selected" @endif>
                        {{ $value }}</option>
                @endforeach
            </select>
            <label for="domain">Max account authorized</label>
        </div>

        <h3 class="large">Assistant</h3>

        @include('parts.form.toggle', ['object' => $sip_domain, 'key' => 'assistant_hide_create_account', 'label' => 'Account creation panel', 'reverse' => true])
        @include('parts.form.toggle', ['object' => $sip_domain, 'key' => 'assistant_disable_qr_code', 'label' => 'QR Code scanning panel', 'reverse' => true])
        @include('parts.form.toggle', ['object' => $sip_domain, 'key' => 'assistant_hide_third_party_account', 'label' => 'Third party SIP panel', 'reverse' => true])

    </form>
@endsection

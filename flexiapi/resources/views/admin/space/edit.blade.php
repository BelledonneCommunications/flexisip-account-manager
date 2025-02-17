@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.spaces.index') }}">Spaces</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('admin.spaces.show', $space->id) }}">
            {{ $space->host }}
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">App Configuration</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph">globe-hemisphere-west</i> {{ $space->host }}</h1>
    </header>

    @include('admin.space.tabs')

    <form method="POST"
        action="{{ route('admin.spaces.update', $space->id) }}"
        accept-charset="UTF-8">
        @csrf
        @method('put')

        <h3 class="large">Features</h3>

        @include('parts.form.toggle', ['object' => $space, 'key' => 'disable_chat_feature', 'label' => 'Chat feature', 'reverse' => true])
        @include('parts.form.toggle', ['object' => $space, 'key' => 'disable_meetings_feature', 'label' => 'Meeting feature', 'reverse' => true])
        @include('parts.form.toggle', ['object' => $space, 'key' => 'disable_broadcast_feature', 'label' => 'Conference feature', 'reverse' => true])
        @include('parts.form.toggle', ['object' => $space, 'key' => 'hide_settings', 'label' => 'General settings', 'reverse' => true])
        @include('parts.form.toggle', ['object' => $space, 'key' => 'hide_account_settings', 'label' => 'Account settings', 'reverse' => true])
        @include('parts.form.toggle', ['object' => $space, 'key' => 'disable_call_recordings_feature', 'label' => 'Record audio/video calls', 'reverse' => true])

        <h3 class="large">General toggles</h3>

        @include('parts.form.toggle', ['object' => $space, 'key' => 'only_display_sip_uri_username', 'label' => 'Only display usernames (hide SIP addresses)'])

        <div class="select">
            <select name="max_account">
                @foreach ([0 => 'No limit', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five'] as $key => $value)
                    <option value="{{ $key }}" @if ($space->max_account == $key) selected="selected" @endif>
                        {{ $value }}</option>
                @endforeach
            </select>
            <label for="domain">Max account authorized</label>
        </div>

        <h3 class="large">Assistant</h3>

        @include('parts.form.toggle', ['object' => $space, 'key' => 'assistant_hide_create_account', 'label' => 'Account creation panel', 'reverse' => true])
        @include('parts.form.toggle', ['object' => $space, 'key' => 'assistant_disable_qr_code', 'label' => 'QR Code scanning panel', 'reverse' => true])
        @include('parts.form.toggle', ['object' => $space, 'key' => 'assistant_hide_third_party_account', 'label' => 'Third party SIP panel', 'reverse' => true])

        <div class="large">
            <input class="btn" type="submit" value="Update">
        </div>
    </form>
@endsection

@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.spaces.index') }}">{{ __('Spaces') }}</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('admin.spaces.show', $space->id) }}">
            {{ $space->host }}
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('App Configuration') }}</li>
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

        <h3 class="large">{{ __('Features') }}</h3>

        @include('parts.form.toggle', ['object' => $space, 'key' => 'disable_chat_feature', 'label' => __('Chat'), 'reversed' => true])
        @include('parts.form.toggle', ['object' => $space, 'key' => 'disable_meetings_feature', 'label' => __('Meeting'), 'reversed' => true])
        @include('parts.form.toggle', ['object' => $space, 'key' => 'disable_broadcast_feature', 'label' => __('Conference'), 'reversed' => true])
        @include('parts.form.toggle', ['object' => $space, 'key' => 'hide_settings', 'label' => __('General settings'), 'reversed' => true])
        @include('parts.form.toggle', ['object' => $space, 'key' => 'hide_account_settings', 'label' => __('Account settings'), 'reversed' => true])
        @include('parts.form.toggle', ['object' => $space, 'key' => 'disable_call_recordings_feature', 'label' => __('Record calls'), 'reversed' => true])

        @include('parts.form.toggle', ['object' => $space, 'key' => 'only_display_sip_uri_username', 'label' => __('Only display usernames (hide SIP addresses)')])

        <div class="select">
            <select name="max_account">
                @foreach ([0 => __('No limit'), 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'] as $key => $value)
                    <option value="{{ $key }}" @if ($space->max_account == $key) selected="selected" @endif>
                        {{ $value }}</option>
                @endforeach
            </select>
            <label for="domain">{{ __('Max accounts') }}</label>
        </div>

        <h3 class="large">{{ __('Assistant') }}</h3>

        @include('parts.form.toggle', ['object' => $space, 'key' => 'assistant_hide_create_account', 'label' => __('Account creation'), 'reversed' => true])
        @include('parts.form.toggle', ['object' => $space, 'key' => 'assistant_disable_qr_code', 'label' => __('QR Code scanning'), 'reversed' => true])
        @include('parts.form.toggle', ['object' => $space, 'key' => 'assistant_hide_third_party_account', 'label' => __('Third party SIP'), 'reversed' => true])

        <div class="large">
            <input class="btn" type="submit" value="{{ __('Update') }}">
        </div>
    </form>
@endsection

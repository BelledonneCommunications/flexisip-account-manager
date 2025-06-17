@extends('layouts.main', ['welcome' => true])

@section('content')
<div id="wizard">
    <h3>{{ __('Configure your Linphone application') }}</h3>

    <a class="btn" href="linphone-config:{{ route('provisioning.provision', ['provisioning_token' => $token]) }}">
        {{ __('Open the app') }}
    </a>
    <a class="btn secondary" target="_blank" href="https://www.linphone.org/en/download/">
        {{ __('Download the app') }}
    </a>
</div>
@endsection

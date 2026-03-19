@extends('layouts.main', ['welcome' => true])

@section('content')
<div id="wizard">
    <h3>{{ __('Configure your Linphone application') }}</h3>

    <a class="btn" href="linphone-config:{{ route('provisioning.provision', ['provisioning_token' => $token]) }}">
        {{ __('Open the app') }}
    </a>
    @if ($platform == 'GNU/Linux')
        <a class="btn secondary" target="_blank" href="https://download.linphone.org/releases/linux/latest_app">
            {{ __('Download Linphone for GNU/Linux') }}
        </a>
    @elseif ($platform == 'Mac')
        <a class="btn secondary" target="_blank" href="https://download.linphone.org/releases/macos/latest_app">
            {{ __('Download Linphone for MacOS') }}
        </a>
    @elseif ($platform == 'Windows')
        <a class="btn secondary" target="_blank" href="https://download.linphone.org/releases/windows/latest_app">
            {{ __('Download Linphone for Windows') }}
        </a>
    @else
        <a class="btn secondary" target="_blank" href="https://www.linphone.org/en/download/">
            {{ __('Download Linphone') }}
        </a>
    @endif
    @if (in_array($platform, ['GNU/Linux', 'MacOS', 'Windows']))
        <p class="center">
            <a target="_blank" href="https://www.linphone.org/en/download/">
                {{ __('Download for another platform') }}
            </a>
        </p>
    @endif
</div>
@endsection

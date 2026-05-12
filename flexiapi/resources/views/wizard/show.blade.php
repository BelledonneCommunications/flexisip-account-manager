@extends('layouts.main', ['welcome' => true])

@section('content')
<div id="wizard">
    @if ($uri->query()->string('linphone-action') == 'call')
        <h3 class="center">{{ __('Start a Linphone call')}}</h3>
    @elseif ($uri->query()->string('linphone-action') == 'show')
        <h3 class="center">{{ __('Open the Linphone application') }}</h3>
    @else
        <h3 class="center">{{ __('Configure your Linphone application') }}</h3>
    @endif

    <a id="open_app" class="btn" href="{{ $uri }}">
        {{ __('Open the app') }}
    </a>

    <script>
        document.querySelector('#open_app').click()
    </script>

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

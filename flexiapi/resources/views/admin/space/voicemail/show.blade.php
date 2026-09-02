@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.spaces.integration')
    <li class="breadcrumb-item active" aria-current="page">{{ __('Voicemails') }}</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph ph-voicemail"></i> {{ $space->name }}</h1>
    </header>

    @if ($space->voicemailAccount)
        <div class="card large">
            <ul>
                <li>
                    <span class="icon">
                        <i class="ph ph-voicemail"></i>
                    </span>
                    <div class="content">
                        <p>
                            {{ __('The Voicemail is enabled and can be reached at:') }}
                            <a href="{{ route('admin.account.show', $space->voicemailAccount->id) }}">
                                {{ $space->voicemailAccount->identifier }}
                            </a>
                        </p>
                    </div>
                </li>
            </ul>
        </div>
    @elseif(!$space->voicemailEnableable())
        <div class="card warning large">
            <ul>
                <li>
                    <span class="icon">
                        <i class="ph ph-warning"></i>
                    </span>
                    <div class="content">
                        <p>{{ __('The Voicemail cannot be enabled') }}</p>
                        <p>{{ __('It seems that you already have an account with the username :username in your Space.', ['username' => \App\Space::VOICEMAIL_USERNAME]) }}</p>
                    </div>
                </li>
            </ul>
        </div>
    @else
        <div class="card large">
            <ul>
                <li>
                    <span class="icon">
                        <i class="ph ph-voicemail"></i>
                    </span>
                    <div class="content">
                        <p>{{ __('Enable') }}</p>
                        <p>{{ __('This will create a new account with the username :username', ['username' => \App\Space::VOICEMAIL_USERNAME]) }}</p>
                    </div>
                    <div class="meta">
                        <a class="btn small oppose" href="{{ route('admin.spaces.voicemail.enable', $space->domain) }}">
                            <i class="ph ph-power"></i> {{ __('Enable') }}
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    @endif

@endsection

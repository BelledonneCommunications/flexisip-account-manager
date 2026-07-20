@extends('layouts.main', ['grid' => true])

@section('content')
    <header>
        <h1><i class="ph ph-user-circle"></i> {{ __('My Account') }}</h1>
    </header>

    <div class="card large">
        <div class="row space-between">
            <div class="column">
                <h2>{{ $account->username }}
                    @if (auth()->user()->superAdmin)
                        <span class="badge badge-error" title="Admin">Super Adm.</span>
                    @elseif (auth()->user()->admin)
                        <span class="badge badge-primary" title="Admin">Adm.</span>
                    @endif
                </h2>
                <div class="row subtitle">
                    <i class="ph ph-at"></i>
                    <span>{{ $account->identifier }}</span>
                    <button type="button" class="btn icon" onclick="copyToClipboard('{{ $account->identifier }}')"><i class="ph ph-copy-simple"></i></button>
                </div>
            </div>
        </div>
    </div>

    {{-- Voice Mail --}}
    <div class="card">
        <div class="header">
            <h3>
                <i class="ph ph-voicemail"></i>
                {{ __('Voicemails') }}
                @if (!$account->uploadedVoicemails->isEmpty()) 
                    <span class="badge badge-main">{{ $account->uploadedVoicemails->count() }}</span> 
                @endif
            </h3>
            <a href="{{ route('account.telephony') }}">{{ __('View all') }} <i class="ph ph-arrow-right"></i> </a>
        </div>
        @if ($account->uploadedVoicemails->isEmpty())
            <div class="empty"><i class="ph ph-voicemail"></i>
                <p>{{ __('No new voicemail') }}</p>
            </div>
        @else
            <ul>
                @foreach ($account->uploadedVoicemails->take(3) as $voicemail)
                    <li>
                        <div class="content">
                            <span class="title">{{ $voicemail->sip_from }}</span>
                            <span class="subtitle">{{ $voicemail->created_at }}</span>
                        </div>
                        @if ($voicemail->url)
                            <a style="margin-left: 1rem;" href="{{ $voicemail->download_url }}" download><i class="ph ph-download"></i></a>
                        @endif
                        <div class="row">
                            <audio class="oppose" controls src="{{ $voicemail->url }}"></audio>
                            <a type="button" class="oppose btn tertiary" 
                            @if ($account->admin)
                                href="{{ route('admin.account.file.delete', [$account, $voicemail->id, 'from' => 'dashboard']) }}" 
                            @else
                                href="{{ route('account.file.delete', [$voicemail->id, 'from' => 'dashboard']) }}" 
                            @endif
                            >
                            <i class="ph ph-trash"></i>
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- My Account --}}
    <div class="card">
        <div class="header">
            <h3><i class="ph ph-identification-card"></i> {{ __('My Account') }} </h3>
        </div>
        <ul>
            <li>
                <span class="icon"><i class="ph ph-envelope"></i></span>
                <div class="content">
                    <span class="title">{{ __('Email') }}</span>
                    <span class="subtitle">
                        @if ($account->email)
                            <p>{{ $account->email }}</p>
                        @else
                            {{ __('No email yet') }}
                        @endif
                    </span>
                </div>
                <div class="meta">
                    <a type="button" class="btn small oppose secondary" href="{{ route('account.email.change') }}">{{ __('Edit') }}</a>
                </div>
            </li>
            <li>
                <span class="icon"><i class="ph ph-phone"></i></span>
                <div class="content">
                    <span class="title">{{ __('Phone number') }}</span>
                    <span class="subtitle">
                        @if ($account->phone)
                            <p>{{ $account->phone }}</p>
                        @else
                            {{ __('No phone yet') }}
                        @endif
                    </span>
                </div>
                <div class="meta">
                    <a type="button" class="btn small oppose secondary" href="{{ route('account.phone.change') }}">{{ __('Edit') }}</a>
                </div>
            </li>
        </ul>
    </div>

    {{-- Devices --}}
    <div class="card">
        <div class="header">
            <h3><i class="ph ph-devices"></i> {{ __('Devices') }} </h3>
            <a href="{{ route('account.device.index') }}">{{ __('View all') }} <i class="ph ph-arrow-right"></i>
            </a>
        </div>
        @if ($devices->isEmpty())
            <div class="empty"><i class="ph ph-devices"></i>
                <p>{{ __('No device') }}</p>
            </div>
        @else
            <ul>
                @foreach ($devices as $device)
                    <li>
                        <div class="icon"> <i
                                class=" ph {{ $device->app_icon }}"></i></div>
                        <div class="content">
                            <span class="title">{{ $device->app_label . ' - ' . $device->version }}</span>
                            <span class="subtitle">{{ $device->device_name . ' - ' . __(':time ago', ['time' => $device->update_time->diffForHumans(now(), true)]) }} </span>
                        </div>
                        <div class="meta">
                            <a type="button" class="btn small oppose secondary"
                                href="{{ route('account.device.delete', [$device->uuid, 'from' => 'dashboard']) }}">
                                <i class="ph ph-trash"></i>
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Call Logs --}}
    <div class="card">
        <div class="header">
            <h3><i class="ph ph-phone-list"></i> {{ __('Calls logs') }}</h3>
            <a href="{{ route('account.statistics.show_call_logs') }}"> {{ __('View all') }} <i class="ph ph-arrow-right"></i> </a>
        </div>
        @if ($calls->isEmpty())
            <div class="empty"><i class="ph ph-phone-list"></i>
                <p>{{ __('No recent calls') }}</p>
            </div>
        @else
            <ul>
                @foreach ($calls as $call)
                    @php
                        $outgoing = $call->from_username === $account->username && $call->from_domain === $account->domain;
                    @endphp
                    <li>
                        <div class="icon {{ $call->state->cssClass() === 'color green' ? 'green' : 'red' }}">
                            <i class="ph ph-phone-{{ $outgoing ? 'outgoing' : 'incoming' }}"></i>
                        </div>
                        <div class="content">
                            <span class="title">{{ $outgoing ? $call->to_username : $call->from_username }}</span>
                            <span class="subtitle">{{ $call->initiated_at }}</span>
                        </div>
                        <div class="meta">
                            @if ($call->ended_at)
                                {{ $call->ended_at->diffForHumans($call->initiated_at, true) }}
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Call Fowarding --}}
    <div class="card">
        @php
            $forwarding = $account->callForwardings->firstWhere('enabled', true);
        @endphp
        <div class="header">
            <h3>
                <i class="ph ph-phone-transfer"></i> {{ __('Call Forwarding') }}
                @if ($forwarding)
                    <span class="badge badge-success" title="Active">{{ __('Active') }}</span>
                @endif
            </h3>
            <a href="{{ route('account.telephony') }}">{{ __('Configure') }} <i class="ph ph-arrow-right"></i></a>
        </div>
        @if ($forwarding)
            <ul>
                <li>
                    <div class="icon"><i class="ph ph-phone-transfer"></i></div>
                    <div class="content">
                        <span class="title">{{ __('Calls are forwarded to') }}</span>
                    </div>
                    <div class="meta">
                        @if ($forwarding->forward_to === 'voicemail')
                            {{ __('Voicemails')}}
                        @elseif ($forwarding->forward_to === 'contact')
                            {{ $forwarding->contact_sip_uri }}
                        @else
                            {{ $forwarding->sip_uri }}
                        @endif
                    </div>
                </li>
            </ul>
        @else
            <div class="empty">
                <i class="ph ph-phone-x"></i>
                <p>{{ __('No call forwarding configured') }}</p>
            </div>
        @endif
    </div>

    {{-- Security --}}
    <div class="card">
        <div class="header">
            <h3><i class="ph ph-shield"></i> {{ __('Security') }}</h3>
        </div>

        <ul>
            <li>
                <span class="icon"><i class="ph ph-lock"></i></span>
                <div class="content">
                    <span class="title">{{ __('Password') }}</span>
                    <span class="subtitle">***************</span>
                </div>
                <div class="meta">
                    <a type="button" class="btn small oppose secondary" href="{{ route('account.password.show') }}">
                        @if ($account->passwords()->count() > 0)
                            {{ __('Edit') }}
                        @else
                            {{ __('Create') }}
                        @endif
                    </a>
                </div>
            </li>
            <li>
                <span class="icon"><i class="ph ph-key"></i></span>
                <div class="content">
                    <span class="title">{{ __('Api Keys') }}</span>
                    <span class="subtitle">***************</span>
                </div>
                <div class="meta">
                    <a type="button" class="btn small oppose secondary" href="{{ route('account.api_keys.show') }}">
                        @if ($account->apiKey())
                            {{ __('Edit') }}
                        @else
                            {{ __('Create') }}
                        @endif
                    </a>
                </div>
            </li>
        </ul>
    </div>

    {{-- Delete Account --}}
    <div class="panel panel-danger large">
        <i class="ph ph-trash"></i>
        <div class="text">
            <span class="title">{{ __('Delete') . ' ' . __('My Account') }}</span>
            <span
                class="description">{{ __('This action is permanent, all your data and configuration will be erased.') }}</span>
        </div>
        <a href="{{ route('account.delete') }}" class="btn small secondary danger"><i class="ph ph-trash"></i>{{ __('Delete') }}</a>
    </div>

@endsection
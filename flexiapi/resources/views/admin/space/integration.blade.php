@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.spaces.integration')
@endsection

@section('content')
    @include('admin.space.head')
    @include('admin.space.tabs')

    <div class="grid third">
        <div class="card">
            @if ($space->emailServer)
                <a class="btn small oppose" href="{{ route('admin.spaces.email.show', $space) }}">{{ __('Edit') }}</a>
            @else
                <a class="btn small oppose secondary" href="{{ route('admin.spaces.email.show', $space) }}">{{ __('Configure') }}</a>
            @endif
            <span class="icon"><i class="ph ph-envelope"></i></span>
            <h3>{{ __('Email Server') }}</h3>
            <p>
                @if ($space->emailServer)
                    {{ $space->emailServer->host}}<br /><br />
                @endif
            </p>
        </div>

        <div class="card">
            @if (!$space->super)
                @if ($space->ssoServer)
                    <a class="btn small oppose secondary" href="{{ route('admin.spaces.sso.show', $space) }}">{{ __('Edit') }}</a>
                @else
                    <a class="btn small oppose secondary" href="{{ route('admin.spaces.sso.show', $space) }}">{{ __('Configure') }}</a>
                @endif
            @else
                <a class="btn small oppose secondary disabled" href="{{ route('admin.spaces.sso.show', $space) }}">{{ __('Disabled (super space)') }}</a>
            @endif
            <span class="icon"><i class="ph ph-key"></i></span>
            <h3>{{ __('SSO Server') }}</h3>
            <p>
                @if ($space->ssoServer)
                    <code>{{ $space->ssoServer->server_url }}</code><br /><br />
                @endif
            </p>
        </div>
    </div>

    <br />

    <a class="btn small oppose" href="{{ route('admin.spaces.carddavs.create', $space) }}">
        <i class="ph ph-plus"></i>
        {{ __('Create') }}
    </a>

    <h3>{{ __('CardDav Servers') }}</h3>

    <div class="grid third">
        @foreach ($space->carddavServers as $carddavServer)
            <div class="card">
                <a class="btn small oppose" href="{{ route('admin.spaces.carddavs.edit', [$space, $carddavServer]) }}">{{ __('Edit') }}</a>
                <a class="btn small oppose tertiary" href="{{ route('admin.spaces.carddavs.delete', [$space, $carddavServer]) }}">{{ __('Delete') }}</a>
                <span class="icon"><i class="ph ph-identification-card"></i></span>
                <h3>{{ $carddavServer->name }}</h3>
                <p>
                    <small class="oppose"><i class="ph ph-users"></i> {{ $carddavServer->accounts()->count() }}</small>
                    {{ $carddavServer->uri}}<br />
                </p>
            </div>
        @endforeach
    </div>
@endsection

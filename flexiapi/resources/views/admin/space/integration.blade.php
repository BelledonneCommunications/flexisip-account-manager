@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.spaces.integration')
@endsection

@section('content')
    @include('admin.space.head')
    @include('admin.space.tabs')

    <div class="grid third">
        <div class="card">
            <a class="btn small oppose secondary" href="{{ route('admin.spaces.email.show', $space) }}">
                <i class="ph ph-pencil"></i>
            </a>
            <h3 class="line"><i class="ph ph-envelope"></i> {{ __('Email Server') }}</h3>
            @if ($space->emailServer)
                <p>
                    {{ $space->emailServer->host}}<br /><br />
                </p>
            @else
                <div class="empty">
                    <i class="ph ph-envelope"></i>
                </div>
            @endif
        </div>

        <div class="card">
            @if (!$space->super)
                <a class="btn small oppose secondary" href="{{ route('admin.spaces.oidc.show', $space) }}">
                    <i class="ph ph-pencil"></i>
                </a>
            @else
                <a class="btn small oppose secondary disabled">
                    {{ __('Disabled (super space)') }}
                </a>
            @endif
            <h3 class="line"><i class="ph ph-key"></i> {{ __('OpenID Connect configuration') }}</h3>
            @if ($space->oidcAuthenticationConfiguration)
                <p>
                    <code>{{ $space->oidcAuthenticationConfiguration->server_url }}</code><br /><br />
                </p>
            @else
                <div class="empty">
                    <i class="ph ph-key"></i>
                </div>
            @endif
        </div>

        <div class="card">
            <a class="btn small oppose secondary" href="{{ route('admin.spaces.digest.show', $space) }}">
                <i class="ph ph-pencil"></i>
            </a>
            <h3 class="line"><i class="ph ph-key"></i> {{ __('Digest configuration') }}</h3>
            @if ($space->digestAuthenticationConfiguration)
                <p>
                    <code>
                        {{ $space->digestAuthenticationConfiguration->realm }}
                    </code>
                     -
                    <i class="ph ph-lock"></i>{{ $space->digestAuthenticationConfiguration->default_password_algorithm }}
                    <br /><br />
                </p>
            @else
                <div class="empty">
                    <i class="ph ph-key"></i>
                </div>
            @endif
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
                <a class="btn small oppose secondary" href="{{ route('admin.spaces.carddavs.edit', [$space, $carddavServer]) }}">
                    <i class="ph ph-pencil"></i>
                </a>
                <a class="btn small oppose tertiary" href="{{ route('admin.spaces.carddavs.delete', [$space, $carddavServer]) }}">
                    <i class="ph ph-trash"></i>
                </a>
                <h3 class="line"><i class="ph ph-identification-card"></i> {{ $carddavServer->name }}</h3>
                <p>
                    <small class="oppose"><i class="ph ph-users"></i> {{ $carddavServer->accounts()->count() }}</small>
                    {{ $carddavServer->uri}}<br />
                </p>
            </div>
        @endforeach
    </div>
@endsection

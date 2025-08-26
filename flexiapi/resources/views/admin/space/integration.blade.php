@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.spaces.integration')
@endsection

@section('content')
    <header>
        <h1><i class="ph ph-globe-hemisphere-west"></i> {{ $space->name }}</h1>
    </header>

    @include('admin.space.tabs')

    <div class="grid third">
        <div class="card">
            <span class="icon"><i class="ph ph-envelope"></i></span>
            <h3>{{ __('Email Server') }}</h3>
            <p>
                @if ($space->emailServer)
                    {{ $space->emailServer->host}}<br /><br />
                @endif
                @if ($space->emailServer)
                    <a class="btn oppose" href="{{ route('admin.spaces.email.show', $space) }}">{{ __('Edit') }}</a>
                    <a class="btn oppose tertiary" href="{{ route('admin.spaces.email.delete', $space) }}">{{ __('Delete') }}</a>
                @else
                    <a class="btn oppose secondary" href="{{ route('admin.spaces.email.show', $space) }}">{{ __('Configure') }}</a>
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
                <small class="oppose"><i class="ph ph-users"></i> {{ $carddavServer->accounts()->count() }}</small>
                <span class="icon"><i class="ph ph-identification-card"></i></span>
                <h3>{{ $carddavServer->name }}</h3>
                <p>
                    {{ $carddavServer->uri}}<br />
                    <br />
                    <a class="btn oppose" href="{{ route('admin.spaces.carddavs.edit', [$space, $carddavServer]) }}">{{ __('Edit') }}</a>
                    <a class="btn oppose tertiary" href="{{ route('admin.spaces.carddavs.delete', [$space, $carddavServer]) }}">{{ __('Delete') }}</a>
                </p>
            </div>
        @endforeach
    </div>
@endsection

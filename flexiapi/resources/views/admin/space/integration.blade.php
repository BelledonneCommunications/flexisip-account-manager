@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.spaces.index') }}">{{ __('Spaces') }}</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('admin.spaces.show', $space->id) }}">
            {{ $space->name }}
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Integration') }}</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph">globe-hemisphere-west</i> {{ $space->name }}</h1>
    </header>

    @include('admin.space.tabs')

    <div class="grid third">
        <div class="card">
            <span class="icon"><i class="ph">envelope</i></span>
            <h3>{{ __('Email Server') }}</h3>
            <p>
                @if ($space->emailServer)
                    {{ $space->emailServer->host}}<br /><br />
                @endif
                @if ($space->emailServer)
                    <a class="btn oppose" href="{{ route('admin.spaces.email.show', $space) }}">{{ __('Edit') }}</a>
                    <a class="btn oppose btn-tertiary" href="{{ route('admin.spaces.email.delete', $space) }}">{{ __('Delete') }}</a>
                @else
                    <a class="btn oppose btn-secondary" href="{{ route('admin.spaces.email.show', $space) }}">{{ __('Configure') }}</a>
                @endif
            </p>
        </div>
    </div>
@endsection

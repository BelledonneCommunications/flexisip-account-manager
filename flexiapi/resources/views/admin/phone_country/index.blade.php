@extends('layouts.main')

@section('content')

<header>
    <h1><i class="ph">flag</i> {{ __('Phone Countries') }}</h1>
    <a class="btn btn-secondary oppose" href="{{ route('admin.phone_countries.activate_all') }}">
        <i class="ph">eye</i> {{ __('Activate All') }}
    </a>
    <a class="btn btn-secondary" href="{{ route('admin.phone_countries.deactivate_all') }}">
        <i class="ph">eye-closed</i> {{ __('Deactivate All') }}
    </a>
</header>

@include('admin.parts.settings_tabs')

<table>
    <thead>
        <tr>
            <th style="width: 100%;">{{ __('Name') }}</th>
            <th>{{ __('Actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($phone_countries as $phone_country)
            <tr>
                <td>
                    @if ($phone_country->activated)
                        <span class="badge badge-success oppose" title="Activated">{{ __('Activated') }}</span>
                    @else
                        <span class="badge badge-error oppose" title="Deactivated">{{ __('Deactivated') }}</span>
                    @endif
                    {{ $phone_country->name }}
                    <small>
                        {{ $phone_country->code }} - {{ $phone_country->country_code }}
                    </small>
                </td>
                <td>
                    @if ($phone_country->activated)
                        <a class="btn btn-secondary small" href="{{ route('admin.phone_countries.deactivate', $phone_country->code) }}">
                            <i class="ph">eye-closed</i>
                        </a>
                    @else
                        <a class="btn btn-secondary small" href="{{ route('admin.phone_countries.activate', $phone_country->code) }}">
                            <i class="ph">eye</i>
                        </a>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection
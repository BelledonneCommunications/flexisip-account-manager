@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item" aria-current="page">
        {{ __('Phone Countries') }}
    </li>
@endsection

@section('content')

<header>
    <h1><i class="ph">flag</i> {{ __('Phone Countries') }}</h1>
    <a class="btn btn-secondary oppose" href="{{ route('admin.phone_countries.activate_all') }}">
        <i class="ph">plus</i>
        {{ __('Activate All') }}
    </a>
    <a class="btn btn-secondary" href="{{ route('admin.phone_countries.deactivate_all') }}">
        <i class="ph">trash</i>
        {{ __('Deactivate All') }}
    </a>
</header>

<table>
    <thead>
        <tr>
            <th>{{ __('Code') }}</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Contry code') }}</th>
            <th>{{ __('Actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($phone_countries as $phone_country)
            <tr>
                <td>{{ $phone_country->code }}</td>
                <td>{{ $phone_country->name }}</td>
                <td>{{ $phone_country->country_code }}</td>
                <td>
                    @if ($phone_country->activated)
                        <span class="badge badge-success" title="Activated">{{ __('Activated') }}</span>
                        <a href="{{ route('admin.phone_countries.deactivate', $phone_country->code) }}">{{ __('Deactivate') }}</a>
                    @else
                        <span class="badge badge-error" title="Deactivated">{{ __('Deactivated') }}</span>
                        <a href="{{ route('admin.phone_countries.activate', $phone_country->code) }}">{{ __('Activate') }}</a>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection
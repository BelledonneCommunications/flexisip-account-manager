@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item" aria-current="page">
        Phone Countries
    </li>
@endsection

@section('content')

<header>
    <h1><i class="material-symbols-outlined">flag</i> Phone Countries</h1>
    <a class="btn btn-secondary oppose" href="{{ route('admin.phone_countries.activate_all') }}">
        <i class="material-symbols-outlined">add_circle</i>
        Activate All
    </a>
    <a class="btn btn-secondary" href="{{ route('admin.phone_countries.deactivate_all') }}">
        <i class="material-symbols-outlined">remove_circle</i>
        Deactivate All
    </a>
</header>

<table>
    <thead>
        <tr>
            <th>Code</th>
            <th>Name</th>
            <th>Country code</th>
            <th>Actions</th>
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
                        <span class="badge badge-success" title="Activated">Activated</span>
                        <a href="{{ route('admin.phone_countries.deactivate', $phone_country->code) }}">Desactivate</a>
                    @else
                        <span class="badge badge-error" title="Deactivated">Deactivated</span>
                        <a href="{{ route('admin.phone_countries.activate', $phone_country->code) }}">Activate</a>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection
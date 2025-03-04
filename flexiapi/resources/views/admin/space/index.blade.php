@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item" aria-current="page">{{ __('Spaces') }}</li>
@endsection

@section('content')

<header>
    <h1><i class="ph">globe-hemisphere-west</i> {{ __('Spaces') }}</h1>
    <a class="btn oppose" href="{{ route('admin.spaces.create') }}">
        <i class="ph">plus</i>
        {{ __('Create') }}
    </a>
</header>

<table>
    <thead>
        <tr>
            <th>{{ __('Space') }}</th>
            <th>{{ __('Host') }}</th>
            <th>{{ __('SIP Domain') }}</th>
            <th>{{ __('Accounts') }}</th>
            <th>{{ __('Expiration') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($spaces as $space)
            <tr>
                <td>
                    <a href="{{ route('admin.spaces.show', $space->id) }}">
                        {{ $space->name }}
                        @if ($space->super) <span class="badge badge-error" title="Super domain">Super</span> @endif
                    </a>
                </td>
                <td>{{ $space->host }}</td>
                <td>{{ $space->domain }}</td>
                <td>
                    {{ $space->accounts_count }} / @if ($space->max_accounts > 0){{ $space->max_accounts }} @else <i class="ph">infinity</i>@endif
                </td>
                <td>
                    @if ($space->isExpired())
                        Expired
                    @elseif ($space->expire_at)
                        In {{ $space->daysLeft }} days
                    @else
                        <i class="ph">infinity</i>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection
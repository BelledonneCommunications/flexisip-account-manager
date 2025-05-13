@extends('layouts.main')

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
            <th>{{ __('SIP Domain') }}</th>
            <th>{{ __('Expiration') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($spaces as $space)
            <tr>
                <td>
                    <a href="{{ route('admin.spaces.show', $space->id) }}">{{ $space->name }}</a>
                    @if ($space->super) <span class="badge badge-error oppose" title="Super domain">Super</span> @endif
                    <br />
                    <small>{{ $space->host }}</small>
                </td>
                <td>{{ $space->domain }}
                    <small>
                        {{ $space->accounts_count }} / @if ($space->max_accounts > 0){{ $space->max_accounts }} @else <i class="ph">infinity</i>@endif<i class="ph">user</i>
                    </small>
                </td>
                <td>
                    @if ($space->isExpired())
                        {{ __('Expired') }}
                    @elseif ($space->expire_at)
                        {{ __('In :days days', ['days' => $space->daysLeft]) }}
                    @else
                        <i class="ph">infinity</i>
                    @endif

                    @if ($space->expire_at)
                        <br />
                        <small>{{ $space->expire_at->format('d-m-Y') }}</small>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection
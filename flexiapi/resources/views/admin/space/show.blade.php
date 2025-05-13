@extends('layouts.main')

@section('breadcrumb')
    @if (auth()->user()->superAdmin)
        <li class="breadcrumb-item">
            <a href="{{ route('admin.spaces.index') }}">{{ __('Spaces') }}</a>
        </li>
    @endif
    <li class="breadcrumb-item">{{ $space->name }}</li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Information') }}</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph">globe-hemisphere-west</i> {{ $space->name }}</h1>

        <a class="btn secondary oppose" @if ($space->isFull())disabled @endif href="{{ route('admin.account.create', ['domain' => $space->domain]) }}">
            <i class="ph">user-plus</i> {{ __('Create') }}
        </a>

        @if (auth()->user()->superAdmin)
            <a class="btn tertiary" href="{{ route('admin.spaces.delete', $space->id) }}">
                <i class="ph">trash</i>
                {{ __('Delete') }}
            </a>
        @endif
    </header>

    @include('admin.space.tabs')

    <div class="grid third  ">
        <div class="card">
            <span class="icon"><i class="ph">users</i></span>
            <h3>{{ __('Users') }}</h3>
            <p>
                {{ $space->accounts()->count() }}
                /
                @if ($space->max_accounts > 0){{ $space->max_accounts }} @else <i class="ph">infinity</i>@endif
            </p>
            @if ($space->max_accounts > 0)
                <progress max="100" value="{{ $space->accountsPercentage }}"
                    class="{{ $space->accountsPercentageClass }}"></progress>
            @endif
        </div>
        <div class="card">
            <span class="icon"><i class="ph">clock</i></span>
            <h3>{{ __('Expiration') }}</h3>
            @if ($space->isExpired())
                <p>Expired</p>
            @elseif ($space->expire_at)
                <p>In {{ $space->daysLeft }} days ({{ $space->expire_at->toDateString() }})</p>
            @else
                <p>{{ __('Never expire') }}</p>
            @endif
        </div>
    </div>

    <a class="btn secondary oppose small" @if ($space->isFull())disabled @endif href="{{ route('admin.account.create', ['admin' => true, 'domain' => $space->domain]) }}"><i class="ph">user-plus</i> {{ __('New Admin') }}</a>

    <h2>{{ __('Admins') }}</h2>

    <table>
        <thead>
            <tr>
                @include('parts.column_sort', ['uriParams' => ['space' => $space], 'key' => 'username', 'title' => __('Identifier')])
                @include('parts.column_sort', ['uriParams' => ['space' => $space], 'key' => 'updated_at', 'title' => __('Updated')])
            </tr>
        </thead>
        <tbody>
            @if ($space->admins->isEmpty())
                <tr class="empty">
                    <td colspan="4">{{ __('Empty') }}</td>
                </tr>
            @endif
            @foreach ($space->admins as $admin)
                <tr>
                    <td>
                        <a href="{{ route('admin.account.show', $admin->id) }}">
                            {{ $admin->identifier }}
                        </a>
                    </td>
                    <td>{{ $admin->updated_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

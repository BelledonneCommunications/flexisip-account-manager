@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
<li class="breadcrumb-item">
    <a href="{{ route('admin.account.edit', $account) }}">{{ $account->identifier }}</a>
</li>
<li class="breadcrumb-item active" aria-current="page">Devices</li>
@endsection

@section('content')

<header>
    <h1><i class="material-symbols-outlined">people</i> {{ $account->identifier }}</h1>
    <a href="{{ route('admin.account.edit', $account->id) }}" class="btn btn-secondary oppose">Cancel</a>
</header>

@include('admin.account.parts.tabs')

<table>
    <thead>
        <tr>
            <th>User Agent</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @if ($devices->isEmpty())
            <tr class="empty">
                <td colspan="3">No Devices</td>
            </tr>
        @endif
        @foreach ($devices as $device)
            <tr>
                <td>{{ $device->user_agent }}</td>
                <td>
                    <a type="button"
                       class="btn"
                       href="{{ route('admin.account.device.delete', [$account, $device->uuid]) }}">
                        Delete
                    </a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection
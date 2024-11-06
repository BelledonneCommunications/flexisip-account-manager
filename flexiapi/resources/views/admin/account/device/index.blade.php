@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    @include('admin.account.parts.breadcrumb_accounts_edit', ['account' => $account])
    <li class="breadcrumb-item active" aria-current="page">Devices</li>
@endsection

@section('content')

<header>
    <h1><i class="ph">users</i> {{ $account->identifier }}</h1>
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
        @else
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
        @endif
    </tbody>
</table>

@endsection
@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Devices</li>
@endsection

@section('content')

    <header>
        <h1><i class="material-symbols-outlined">devices</i> Devices management</h1>
    </header>

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
                            <a type="button" class="btn" href="{{ route('account.device.delete', [$device->uuid]) }}">
                                Delete
                            </a>
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

@endsection

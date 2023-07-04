@extends('layouts.main')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Devices</li>
@endsection

@section('content')

<table class="table">
    <thead>
        <tr>
            <th>User Agent</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($devices as $device)
            <tr>
                <td>{{ $device->user_agent }}</td>
                <td>
                    <a type="button"
                       class="btn btn-danger"
                       href="{{ route('account.device.delete', $device->uuid) }}">
                        Delete
                    </a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>


@endsection
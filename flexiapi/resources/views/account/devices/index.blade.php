@extends('layouts.account')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Devices</li>
@endsection

@section('content')

<table class="table table-responsive-md">
    <thead>
        <tr>
            <th scope="col">User Agent</th>
            <th scope="col">Expires At</th>
            <th scope="col"></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($devices as $device)
            <tr>
                <td>{{ $device->user_agent }}</td>
                <td>{{ $account->expires_at }}</td>
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
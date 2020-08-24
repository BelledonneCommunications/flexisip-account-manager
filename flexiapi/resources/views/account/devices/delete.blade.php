@extends('layouts.account')

@section('breadcrumb')
<li class="breadcrumb-item" aria-current="page">
    <a href="{{ route('account.device.index') }}">Devices</a>
</li>
<li class="breadcrumb-item active" aria-current="page">Delete</li>
@endsection

@section('content')

<h2>Device deletion</h2>

<p>Are you sure you want to delete the following device?</p>
<p>
    <b>User Agent:</b> {{ $device->user_agent }}<br />
    <b>Expires At:</b> {{ $device->expires_at }}</p>
</p>


{!! Form::open(['route' => 'account.device.destroy', 'method' => 'delete']) !!}

{!! Form::hidden('uuid', $device->uuid) !!}

{!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
{!! Form::close() !!}


@endsection
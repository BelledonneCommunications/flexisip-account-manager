@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.account.device.index', $account) }}">Devices</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Delete</li>
@endsection

@section('content')
    <h2>Device deletion</h2>

    <div>
        <p>Are you sure you want to delete the following device?</p>
        <p>
            <b>User Agent:</b> {{ $device->user_agent }}
        </p>
    </div>

    <form method="POST" action="{{ route('admin.account.device.destroy', $account) }}" accept-charset="UTF-8">
        @method('delete')
        @csrf
        <input name="uuid" type="hidden" value="{{ $device->uuid }}">
        <div>
            <input class="btn" type="submit" value="Delete">
        </div>

    </form>
@endsection

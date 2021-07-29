@extends('layouts.account')

@section('breadcrumb')
<li class="breadcrumb-item" aria-current="page">
    <a href="{{ route('admin.account.index') }}">Accounts</a>
</li>
<li class="breadcrumb-item" aria-current="page">
    <a href="{{ route('admin.account.show', $account->id) }}">{{ $account->identifier }}</a>
</li>
@endsection

@section('content')

<a class="btn btn-danger float-right" href="{{ route('admin.account.delete', $account->id) }}">Delete</a>
<a class="btn float-right mr-2" href="{{ route('admin.account.edit', $account->id) }}">Edit</a>

<h2>Account</h2>

<p>
    <b>Id:</b> {{ $account->id }}<br />
    <b>Identifier:</b> {{ $account->identifier }}<br />
    <b>Email:</b> <a href="mailto:{{ $account->email }}">{{ $account->email }}</a><br />
    <b>Phone number:</b>@if ($account->alias) {{ $account->phone }} @else No number @endif
</p>

@if ($account->sha256Password)
    <span class="badge badge-info">SHA256</span>
@endif

<br />

@if ($account->activated)
    <span class="badge badge-success">Activated</span> <a href="{{ route('admin.account.deactivate', $account->id) }}">Deactivate</a>
@else
    <span class="badge badge-danger">Unactivated</span> <a href="{{ route('admin.account.activate', $account->id) }}">Activate</a>
@endif

<br />

@if ($account->admin)
    <span class="badge badge-success">Admin</span> <a href="{{ route('admin.account.unadmin', $account->id) }}">Remove admin role</a>
@else
    <span class="badge badge-danger">Not Admin</span> <a href="{{ route('admin.account.admin', $account->id) }}">Add admin role</a>
@endif

@if ($account->confirmation_key)
    <h3 class="mt-3">Provisioning</h3>
    <p>Share the following picture with the user or the one-time-use link bellow.</p>

    <img src="{{ route('provisioning.qrcode', $account->confirmation_key) }}"><br />

    <br />
    <p>The following link can only be visited once</p>
    <input class="form-control" type="text" readonly value="{{ route('provisioning.show', $account->confirmation_key) }}">
    <p class="mt-3">
        <a class="btn btn-light mr-2" href="{{ route('admin.account.provision', $account->id) }}">Renew the provision link</a>
        The current one will be unavailable
    </p>
@else
    <p class="mt-3">
        <a class="btn btn-light" href="{{ route('admin.account.provision', $account->id) }}">Generate a provision link</a>
    </p>
@endif

@endsection
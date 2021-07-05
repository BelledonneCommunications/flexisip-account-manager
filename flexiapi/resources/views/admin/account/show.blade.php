@extends('layouts.account')

@section('breadcrumb')
<li class="breadcrumb-item" aria-current="page">
    <a href="{{ route('admin.account.index') }}">Accounts</a>
</li>
<li class="breadcrumb-item active" aria-current="page">Show</li>
@endsection

@section('content')

<h2>Account</h2>

<p>
    <b>Id:</b> {{ $account->id }}<br />
    <b>Identifier:</b> {{ $account->identifier }}<br />
    <b>Email:</b> {{ $account->email }}</p>
</p>

@if ($account->alias)
    <p><b>Alias:</b> {{ $account->alias->alias }}</p>
@else
    <p>No alias</p>
@endif

<p>
    @if ($account->activated)
        <span class="badge badge-success">Activated</span> <a href="{{ route('admin.account.deactivate', $account->id) }}">Deactivate</a>
    @else
        <span class="badge badge-danger">Unactivated</span> <a href="{{ route('admin.account.activate', $account->id) }}">Activate</a>
    @endif
</p>

<p>
    @if ($account->admin)
        <span class="badge badge-success">Admin</span> <a href="{{ route('admin.account.unadmin', $account->id) }}">Remove admin role</a>
    @else
        <span class="badge badge-danger">Not Admin</span> <a href="{{ route('admin.account.admin', $account->id) }}">Add admin role</a>
    @endif
</p>

<a class="btn btn-danger" href="{{ route('admin.account.delete', $account->id) }}">Delete the account</a>

@if ($account->confirmation_key)
    <h3 class="mt-3">Provisioning</h3>
    <p>Share the following picture with the user or the one-time-use link bellow.</p>

    <img src="{{ route('provisioning.qrcode', $account->confirmation_key) }}"><br />

    <br />
    <p>The following link can only be visited once</p>
    <input class="form-control" type="text" readonly value="{{ route('provisioning.show', $account->confirmation_key) }}">
@endif

@endsection
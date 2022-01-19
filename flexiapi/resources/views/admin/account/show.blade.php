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
    <b>DTMF Protocol:</b> @if ($account->dtmf_protocol) {{ $account->resolvedDtmfProtocol }}@endif<br />
    @if ($account->alias)<b>Phone number:</b> {{ $account->phone }}<br />@endif
    @if ($account->display_name)<b>Display name:</b> {{ $account->display_name }}<br />@endif
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

<h3 class="mt-3">Contacts</h3>

<table class="table">
    <tbody>
        @foreach ($account->contacts as $contact)
            <tr>
                <th scope="row">{{ $contact->identifier }}</th>
                <td>
                    <a class="btn btn-sm mr-2" href="{{ route('admin.account.contact.delete', [$account, $contact->id]) }}">Delete</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<a class="btn btn-sm" href="{{ route('admin.account.contact.create', $account) }}">Add</a>

<h3 class="mt-3">Actions</h3>

@if (!$account->dtmf_protocol)

<table class="table">
    <tbody>
        @foreach ($account->actions as $action)
            <tr>
                <th scope="row">{{ $action->key }}</th>
                <td>{{ $action->code }}</td>
                <td>
                    <a class="btn btn-sm mr-2" href="{{ route('admin.account.action.edit', [$account, $action->id]) }}">Edit</a>
                    <a class="btn btn-sm mr-2" href="{{ route('admin.account.action.delete', [$account, $action->id]) }}">Delete</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<a class="btn btn-sm" href="{{ route('admin.account.action.create', $account) }}">Add</a>

@else
    <p>To manage actions, you must configure the DTMF protocol in the account settings.</p>
@endif

<h3 class="mt-3">Types</h3>

<table class="table">
    <tbody>
        @foreach ($account->types as $type)
            <tr>
                <th scope="row">{{ $type->key }}</th>
                <td>
                    {!! Form::open(['route' => ['admin.account.account_type.destroy', $account, $type->id], 'method' => 'delete']) !!}
                    {!! Form::submit('Delete', ['class' => 'btn btn-sm mr-2']) !!}
                    {!! Form::close() !!}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<a class="btn btn-sm" href="{{ route('admin.account.account_type.create', $account) }}">Add</a>

<h3 class="mt-3">Provisioning</h3>

@if ($account->confirmation_key)
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
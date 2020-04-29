@extends('layouts.account')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Accounts</li>
@endsection

@section('content')

<h2>Accounts</h2>

<table class="table">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Identifier</th>
            <th scope="col">Email</th>
            <th scope="col">Created</th>
            <th scope="col">Tags</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($accounts as $account)
            <tr>
                <th scope="row">
                <a href="{{ route('admin.account.show', $account->id) }}">{{ $account->id }}</a>
                </th>
                <td>{{ $account->identifier }}</td>
                <td>{{ $account->email }}</td>
                <td>{{ $account->creation_time}}</td>
                <td>
                    @if ($account->activated)
                        <span class="badge badge-success">Activated</span>
                    @else
                        <span class="badge badge-danger">Unactivated</span>
                    @endif
                    @if ($account->admin)
                        <span class="badge badge-primary">Admin</span>
                    @endif
                </td>
            </tr>
        @endforeach

    </tbody>
</table>

{{ $accounts->links() }}

@endsection
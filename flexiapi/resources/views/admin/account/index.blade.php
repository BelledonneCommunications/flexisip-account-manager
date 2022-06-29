@extends('layouts.account')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Accounts</li>
@endsection

@section('content')

<div class="row mb-2">
    <div class="col-sm">
        <a class="btn btn-success float-right" href="{{ route('admin.account.create') }}">Create</a>
        <h2>Accounts</h2>
    </div>
    <div class="col-sm">
        {!! Form::open(['route' => 'admin.account.search']) !!}
            <div class="form-row">
                <div class="col-8">
                    {!! Form::text('search', $search, ['class' => 'form-control', 'placeholder' => 'Search by username: +1234, foo_bar…']) !!}
                </div>
                <div class="col-4">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </div>
        {!! Form::close() !!}
    </div>
</div>

<table class="table table-responsive-md">
    <thead>
        <tr>
            <th scope="col">Identifier (email)</th>
            <th scope="col"></th>
            <th scope="col">Created</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($accounts as $account)
            <tr>
                <td>
                    <a href="{{ route('admin.account.show', $account->id) }}">
                        {{ $account->identifier }}
                    </a>
                </td>
                <td>
                    @if ($account->externalAccount)
                        <span class="badge badge-secondary" title="External Account attached">EA</span>
                    @endif
                    @if ($account->email)
                        <span class="badge badge-info">Email</span>
                    @endif
                    @if ($account->activated)
                        <span class="badge badge-success" title="Activated">Act.</span>
                    @endif
                    @if ($account->admin)
                        <span class="badge badge-primary" title="Admin">Adm.</span>
                    @endif
                    @if ($account->sha256Password)
                        <span class="badge badge-info">SHA256</span>
                    @endif
                </td>
                <td>{{ $account->creation_time}}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{ $accounts->links('pagination::bootstrap-4') }}

@endsection
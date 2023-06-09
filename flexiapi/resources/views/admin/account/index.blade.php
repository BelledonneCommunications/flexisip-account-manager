@extends('layouts.main')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Accounts</li>
@endsection

@section('content')

    <div>
        <a class="btn oppose" href="{{ route('admin.account.create') }}">
            <i class="material-icons">add_circle</i>
            Create
        </a>
        <h1><i class="material-icons">people</i> Account</h1>
    </div>
    <div>
        {!! Form::open(['route' => 'admin.account.search']) !!}
            <div>
                {!! Form::text('search', $search, ['placeholder' => 'Search by username: +1234, foo_bar…']) !!}
                {!! Form::label('search', 'Search') !!}
            </div>
            <div>
                <button type="submit" class="btn oppose">Search</button>
            </div>
        {!! Form::close() !!}
    </div>

    <br />

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
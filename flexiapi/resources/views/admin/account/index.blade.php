@extends('layouts.main')

@section('content')

    <header>
        <h1><i class="material-icons">people</i> Account</h1>
        <a class="btn oppose" href="{{ route('admin.account.create') }}">
            <i class="material-icons">add_circle</i>
            Create
        </a>
    </header>
    <div>
        <form class="inline" method="POST" action="{{ route('admin.account.search')}}" accept-charset="UTF-8">
            @csrf
            <div>
                <input placeholder="Search by username: +1234, foo_bar…" name="search" type="text" value="{{ $search }}">
                <label for="search">Search</label>
            </div>
            <div>
                <input name="updated_date" type="date" value="{{ $updated_date }}">
                <label for="updated_date">Updated Date</label>
            </div>
            <div>
                <a href="{{ route('admin.account.index')}}" type="reset" class="btn btn-secondary">Reset</a>
                <button type="submit" class="btn">Search</button>
            </div>
        </form>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Identifier (email)</th>
                <th></th>
                <th>
                    <a href="{{ route('admin.account.index', ['updated_at_order' => $updated_at_order]) }}">
                        Updated
                        @if ($updated_at_order == 'desc')
                            <i class="material-icons">expand_more</i>
                        @else
                            <i class="material-icons">expand_less</i>
                        @endif
                    </a>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($accounts as $account)
                <tr>
                    <td>
                        <a href="{{ route('admin.account.edit', $account->id) }}">
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
                    <td>{{ $account->updated_at}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $accounts->links('pagination::bootstrap-4') }}

@endsection
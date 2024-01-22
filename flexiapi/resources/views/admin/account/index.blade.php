@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item" aria-current="page">
        Accounts
    </li>
@endsection

@section('content')
    <header>
        <h1><i class="material-symbols-outlined">people</i> Accounts</h1>
        <a class="btn btn-secondary oppose" href="{{ route('admin.account.import.create') }}">
            <i class="material-symbols-outlined">publish</i>
            Import Accounts
        </a>
        @if(config('app.intercom_features'))
        <a class="btn btn-secondary" href="{{ route('admin.account.type.index') }}">
            <i class="material-symbols-outlined">category</i>
            Types
        </a>
        @endif
        <a class="btn" href="{{ route('admin.account.create') }}">
            <i class="material-symbols-outlined">add_circle</i>
            New Account
        </a>
    </header>
    <div>
        <form class="inline" method="POST" action="{{ route('admin.account.search') }}" accept-charset="UTF-8">
            @csrf
            <input type="hidden" name="order_by" value="{{ request()->get('order_by', '') }}">
            <input type="hidden" name="order_sort" value="{{ request()->get('order_sort', '') }}">
            <div class="search large">
                <input placeholder="Search by username: +1234, foo_bar…" name="search" type="text"
                    value="{{ request()->get('search', '') }}">
                <label for="search">Search</label>
            </div>
            <div class="large on_desktop"></div>
            <div class="select">
                <select name="domain" onchange="this.form.submit()">
                    <option value="">
                        Select a domain
                    </option>
                    @foreach ($domains as $d)
                        <option value="{{ $d }}" @if (request()->get('domain', '') == $d) selected="selected" @endif>
                            {{ $d }}
                        </option>
                    @endforeach
                </select>
                <label for="domain">Domain</label>
            </div>
            <div class="select">
                <select name="contacts_list" onchange="this.form.submit()">
                    <option value="">
                        Select a contacts list
                    </option>
                    @foreach ($contacts_lists as $key => $name)
                        <option value="{{ $key }}" @if (request()->get('contacts_list', '') == $key) selected="selected" @endif>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                <label for="domain">Contacts list</label>
            </div>
            <div>
                <input name="updated_date" type="date" value="{{ request()->get('updated_date', '') }}"
                    onchange="this.form.submit()">
                <label for="updated_date">Updated Date</label>
            </div>
            <div class="oppose">
                <a href="{{ route('admin.account.index') }}" type="reset" class="btn btn-tertiary">Reset</a>
                <button type="submit" class="btn">Search</button>
            </div>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                @include('parts.column_sort', ['key' => 'username', 'title' => 'Identifier'])
                <th>Contact lists</th>
                <th>Badges</th>
                @include('parts.column_sort', ['key' => 'updated_at', 'title' => 'Updated'])
            </tr>
        </thead>
        <tbody>
            @if ($accounts->isEmpty())
                <tr class="empty">
                    <td colspan="4">No Accounts</td>
                </tr>
            @endif
            @foreach ($accounts as $account)
                <tr>
                    <td>
                        <a href="{{ route('admin.account.edit', $account->id) }}">
                            {{ $account->identifier }}
                        </a>
                    </td>
                    <td>
                        @if ($account->contactsLists->isNotEmpty())
                            {{ $account->contactsLists->first()->title }}
                            @if ($account->contactsLists->count() > 1)
                                <span class="badge">+{{ $account->contactsLists->count() - 1 }}</span>
                            @endif
                        @endif
                    </td>
                    <td>
                        @if ($account->activated)
                            <span class="badge badge-success" title="Activated">Act.</span>
                        @endif
                        @if ($account->admin)
                            <span class="badge badge-primary" title="Admin">Adm.</span>
                        @endif
                        @if ($account->sha256Password)
                            <span class="badge badge-info">SHA256</span>
                        @endif
                        @if ($account->blocked)
                            <span class="badge badge-error">Blocked</span>
                        @endif
                    </td>
                    <td>{{ $account->updated_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $accounts->links('pagination::bootstrap-4') }}
@endsection

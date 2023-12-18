@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.contacts_lists.index') }}">Contacts Lists</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('admin.contacts_lists.edit', $contacts_list->id) }}">{{ $contacts_list->title }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Add contacts</li>
@endsection

@section('content')
    <header>
        <h1><i class="material-icons-outlined">account_box</i> {{ $contacts_list->title }}</h1>

        <a href="{{ route('admin.contacts_lists.edit', $contacts_list->id) }}" class="btn btn-secondary oppose">Cancel</a>

        <form method="POST" action="{{ route('admin.contacts_lists.contacts.store', $contacts_list->id) }}"
            name="contacts_lists_contacts_store" accept-charset="UTF-8">
            @csrf
            @method('post')

            <select name="contacts_ids[]" class="list_toggle" data-list-id="a{{ $contacts_list->id }}"></select>
            <input type="hidden" name="contacts_list_id" value="{{ $contacts_list->id }}">
        </form>
    </header>

    <div>
        <form class="inline" method="POST" action="{{ route('admin.contacts_lists.contacts.search', $params) }}"
            accept-charset="UTF-8">
            @csrf
            <div class="search">
                <input placeholder="Search by username: +1234, foo_bar…" name="search" type="text"
                    value="{{ request()->get('search', '') }}">
                <label for="search">Search</label>
            </div>
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
            <div>
                <a href="{{ route('admin.contacts_lists.contacts.add', $contacts_list->id) }}" type="reset"
                    class="btn btn-secondary">Reset</a>
                <button type="submit" class="btn">Search</button>
            </div>
            <div class="oppose">
                <a class="btn"
                    onclick="Utils.clearStorageList('a{{ $contacts_list->id }}'); document.querySelector('form[name=contacts_lists_contacts_store]').submit()">
                    <i class="material-icons-outlined">add_circle</i>
                    Add <span class="list_toggle" data-list-id="a{{ $contacts_list->id }}"></span> contacts
                </a>
            </div>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th width="1%">
                    <input type="checkbox" onchange="Utils.toggleAll(this)">
                </th>
                <th>Username</th>
                @include('parts.column_sort', [
                    'key' => 'updated_at',
                    'title' => 'Updated',
                    'uriParams' => ['contacts_list_id' => $contacts_list->id],
                ])
            </tr>
        </thead>
        <tbody>
            @if ($accounts->isEmpty())
                <tr class="empty">
                    <td colspan="3">No Contact</td>
                </tr>
            @endif
            @foreach ($accounts as $account)
                <tr>
                    <td>
                        <input class="list_toggle" type="checkbox" data-list-id="a{{ $contacts_list->id }}"
                            data-id="{{ $account->id }}">
                    </td>
                    <td>
                        {{ $account->identifier }}
                    </td>
                    <td>{{ $account->updated_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $accounts->links('pagination::bootstrap-4') }}
@endsection

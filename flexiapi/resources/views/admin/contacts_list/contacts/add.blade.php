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
        <h1><i class="material-icons">account_box</i> {{ $contacts_list->title }}</h1>

        <a href="{{ route('admin.contacts_lists.edit', $contacts_list->id) }}" class="btn btn-secondary oppose">Cancel</a>

        <form method="POST"
        action="{{ route('admin.contacts_lists.contacts.store', $contacts_list->id) }}"
        name="contacts_lists_contacts_store"
        accept-charset="UTF-8">
            @csrf
            @method('post')

            <select name="contacts_ids[]" class="list_toggle" data-list-id="a{{ $contacts_list->id }}"></select>
            <input type="hidden" name="contacts_list_id" value="{{ $contacts_list->id }}">
        </form>
    </header>

    <div>
        <form class="inline" method="POST" action="{{ route('admin.contacts_lists.contacts.search', $params) }}" accept-charset="UTF-8">
            @csrf
            <div class="search large">
                <input placeholder="Search by username: +1234, foo_bar…" name="search" type="text" value="{{ $params['search'] }}">
                <label for="search">Search</label>
            </div>
            <div>
                <a href="{{ route('admin.contacts_lists.contacts.add', $contacts_list->id) }}" type="reset" class="btn btn-secondary">Reset</a>
                <button type="submit" class="btn">Search</button>
            </div>
            <div class="oppose">
                <p style="display: inline-block; margin: 0 1rem;">
                    <span class="list_toggle" data-list-id="a{{ $contacts_list->id }}"></span> selected
                </p>
                <a class="btn" onclick="Utils.clearStorageList('a{{ $contacts_list->id }}'); document.querySelector('form[name=contacts_lists_contacts_store]').submit()">
                    <i class="material-icons">add_circle</i>
                    Add
                </a>
            </div>
        </form>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th width="1%">
                    <input type="checkbox" onchange="Utils.toggleAll(this)">
                </th>
                <th>Username</th>
                <th>
                    <a href="{{ route('admin.contacts_lists.contacts.add', $params) }}">
                        Updated
                        @if ($params['updated_at_order'] == 'desc')
                            <i class="material-icons">expand_more</i>
                        @else
                            <i class="material-icons">expand_less</i>
                        @endif
                    </a>
                </th>
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
                        <input class="list_toggle" type="checkbox" data-list-id="a{{ $contacts_list->id }}" data-id="{{ $account->id }}">
                    </td>
                    <td>
                        {{ $account->identifier }}
                    </td>
                    <td>{{ $account->updated_at}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $accounts->links('pagination::bootstrap-4') }}
@endsection
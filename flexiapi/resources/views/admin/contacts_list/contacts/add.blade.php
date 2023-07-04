@extends('layouts.main')

@section('content')
    <header>
        <h1><i class="material-icons">account_box</i> Contacts List | Add contacts</h1>
        <p class="oppose">
            <span class="list_toggle" data-list-id="a{{ $contacts_list->id }}"></span> selected
        </p>

        <form method="POST"
        action="{{ route('admin.contacts_lists.contacts.store', $contacts_list->id) }}"
        accept-charset="UTF-8">
            @csrf
            @method('post')

            <select name="contacts_ids[]" class="list_toggle" data-list-id="a{{ $contacts_list->id }}"></select>
            <input type="hidden" name="contacts_list_id" value="{{ $contacts_list->id }}">
            <input class="btn" type="submit" value="Add" onclick="Utils.clearStorageList('a{{ $contacts_list->id }}')">
        </form>
    </header>

    <div>
        <form class="inline" method="POST" action="{{ route('admin.contacts_lists.contacts.search', $params) }}" accept-charset="UTF-8">
            @csrf
            <div>
                <input placeholder="Search by username: +1234, foo_bar…" name="search" type="text" value="{{ $params['search'] }}">
                <label for="search">Search</label>
            </div>
            <div>
                <a href="{{ route('admin.contacts_lists.contacts.add', $contacts_list->id) }}" type="reset" class="btn btn-secondary">Reset</a>
                <button type="submit" class="btn">Search</button>
            </div>
        </form>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>
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
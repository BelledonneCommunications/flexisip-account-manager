@extends('layouts.main')

@section('content')
    <header>
        @if ($contacts_list->id)
            <h1><i class="material-icons">account_box</i> Edit a Contacts List</h1>
            <a class="btn oppose btn-secondary" href="{{ route('admin.contacts_lists.delete', $contacts_list->id) }}">
                <i class="material-icons">delete</i>
                Delete
            </a>
        @else
            <h1><i class="material-icons">account_box</i> Create a Contacts List</h1>
        @endif
    </header>

    @if ($contacts_list->id)
        <p title="{{ $contacts_list->updated_at }}">Updated on {{ $contacts_list->updated_at->format('d/m/Y') }}
    @endif

    <form method="POST"
        action="{{ $contacts_list->id ? route('admin.contacts_lists.update', $contacts_list->id) : route('admin.contacts_lists.store') }}"
        accept-charset="UTF-8">
        @csrf
        @method($contacts_list->id ? 'put' : 'post')
        <div>
            <input placeholder="Name" required="required" name="title" type="text" value="{{ $contacts_list->title }}">
            <label for="username">Name</label>
            @include('parts.errors', ['name' => 'title'])
        </div>

        <div>
            <textarea placeholder="Description" required="required" name="description">{{ $contacts_list->description }}</textarea>
            <label for="description">Description</label>
            @include('parts.errors', ['name' => 'description'])
        </div>

        <div class="large">
            <input class="btn oppose" type="submit" value="{{ $contacts_list->id ? 'Update' : 'Create' }}">
        </div>
    </form>

    @if ($contacts_list->id)
        <hr class="clear">

        <header>
            <p class="oppose">
                <span class="list_toggle" data-list-id="d{{ $contacts_list->id }}"></span> selected
            </p>

            <form method="POST"
            action="{{ route('admin.contacts_lists.contacts.destroy', $contacts_list->id) }}"
            accept-charset="UTF-8">
                @csrf
                @method('delete')

                <select name="contacts_ids[]" class="list_toggle" data-list-id="d{{ $contacts_list->id }}"></select>
                <input type="hidden" name="contacts_list_id" value="{{ $contacts_list->id }}">
                <input class="btn btn-tertiary" type="submit" value="Remove" onclick="Utils.clearStorageList('d{{ $contacts_list->id }}')">
            </form>

            <a class="btn btn-secondary" href="{{ route('admin.contacts_lists.contacts.add', $contacts_list->id) }}">
                <i class="material-icons">add</i> Add contacts
            </a>
        </header>

        <table class="large">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" onchange="Utils.toggleAll(this)">
                    </th>
                    <th>Username</th>
                </tr>
            </thead>
            <tbody>
                @if ($contacts_list->contacts->isEmpty())
                    <tr class="empty">
                        <td colspan="2">No Contact</td>
                    </tr>
                @endif
                @foreach ($contacts_list->contacts as $contact)
                    <tr>
                        <td>
                            <input class="list_toggle" type="checkbox" data-list-id="d{{ $contacts_list->id }}" data-id="{{ $contact->id }}">
                        </td>
                        <td>{{ $contact->identifier }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection

@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.contacts_lists.index') }}">Contacts Lists</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Delete</li>
@endsection

@section('content')
    <h2>Delete a Contact List</h2>

    <form method="POST" action="{{ route('admin.contacts_lists.destroy', $contacts_list->id) }}" accept-charset="UTF-8">
        @csrf
        @method('delete')

        <div class="large">
            <p>You are going to permanently delete the following contacts list. Please confirm your action.<br />
                <b>{{ $contacts_list->title }}</b>
            </p>

            <input name="contacts_lists_id" type="hidden" value="{{ $contacts_list->id }}">
        </div>
        <div>
            <a href="{{ route('admin.contacts_lists.edit', $contacts_list->id) }}" class="btn btn-secondary">Cancel</a>
            <input class="btn" type="submit" value="Delete">
        </div>

    </form>
@endsection

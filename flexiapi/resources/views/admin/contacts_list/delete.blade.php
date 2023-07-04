@extends('layouts.main')

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
            <input class="btn" type="submit" value="Delete">
        </div>

    </form>
@endsection

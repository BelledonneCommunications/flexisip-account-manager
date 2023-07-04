@extends('layouts.main')

@section('content')

<header>
    <h1><i class="material-icons">account_box</i> Contacts Lists</h1>
    <a class="btn oppose" href="{{ route('admin.contacts_lists.create') }}">
        <i class="material-icons">add_circle</i>
        Create
    </a>
</header>

<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Number of Contacts</th>
            <th>
                <a href="{{ route('admin.contacts_lists.index', ['updated_at_order' => $updated_at_order]) }}">
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
        @foreach ($contacts_lists as $contacts_list)
            <tr>
                <td>
                    <a href="{{ route('admin.contacts_lists.edit', $contacts_list->id) }}">
                        {{ $contacts_list->title }}
                    </a>
                </td>
                <td class="line">{{ $contacts_list->description }}</td>
                <td>{{ $contacts_list->contacts_count }}</td>
                <td>{{ $contacts_list->updated_at}}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{ $contacts_lists->links('pagination::bootstrap-4') }}

@endsection
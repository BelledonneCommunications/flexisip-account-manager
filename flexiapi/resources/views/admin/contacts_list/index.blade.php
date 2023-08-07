@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item" aria-current="page">
        Contacts Lists
    </li>
@endsection

@section('content')

<header>
    <h1><i class="material-icons">account_box</i> Contacts Lists</h1>
    <a class="btn oppose" href="{{ route('admin.contacts_lists.create') }}">
        <i class="material-icons">add_circle</i>
        New Contacts List
    </a>
</header>

<table class="table">
    <thead>
        <tr>
            @include('parts.column_sort', ['key' => 'title', 'title' => 'Name'])
            <th>Description</th>
            @include('parts.column_sort', ['key' => 'contacts_count', 'title' => 'Contacts'])
            @include('parts.column_sort', ['key' => 'updated_at', 'title' => 'Updated'])
        </tr>
    </thead>
    <tbody>
        @if ($contacts_lists->isEmpty())
            <tr class="empty">
                <td colspan="4">No Contacts Lists</td>
            </tr>
        @endif
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
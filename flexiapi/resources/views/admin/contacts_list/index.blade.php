@extends('layouts.main')

@section('content')

<header>
    <h1><i class="ph ph-user-rectangle"></i> {{ __('Contacts Lists') }}</h1>
    <a class="btn oppose" href="{{ route('admin.contacts_lists.create') }}">
        <i class="ph ph-plus"></i>
        {{ __('Create') }}
    </a>
</header>

<table>
    <thead>
        <tr>
            @include('parts.column_sort', ['key' => 'title', 'title' => __('Name')])
            <th>{{ __('Description') }}</th>
            @include('parts.column_sort', ['key' => 'contacts_count', 'title' => __('Contacts')])
            @include('parts.column_sort', ['key' => 'updated_at', 'title' => __('Updated')])
        </tr>
    </thead>
    <tbody>
        @if ($contacts_lists->isEmpty())
            <tr class="empty">
                <td colspan="4">{{ __('Empty') }}</td>
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
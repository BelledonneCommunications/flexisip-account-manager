@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.spaces.show')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.spaces.contacts_lists.index', $space) }}">{{ __('Contacts Lists') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        @if ($contacts_list->id)
            {{ __('Edit') }}
        @else
            {{ __('Create') }}
        @endif
    </li>
@endsection

@section('content')
    @include('admin.space.head')
    @include('admin.space.tabs')

    <header>
        @if ($contacts_list->id)
            <h1><i class="ph ph-user-rectangle"></i> {{ $contacts_list->title }}</h1>
            <a class="btn secondary oppose" title="{{ __('Delete') }}"
                href="{{ route('admin.spaces.contacts_lists.delete', [$space, $contacts_list->id]) }}">
                <i class="ph ph-trash"></i>
            </a>
            <input form="create_edit_contacts_list" class="btn" type="submit" value="{{ __('Update') }}">
        @else
            <h1><i class="ph ph-user-rectangle"></i> {{ __('Create') }}</h1>
            <input form="create_edit_contacts_list" class="btn oppose" type="submit" value="{{ __('Create') }}">
        @endif
    </header>

    @if ($contacts_list->id)
        <p title="{{ $contacts_list->updated_at }}">{{ __('Updated on') }} {{ $contacts_list->updated_at->format('d/m/Y') }}
    @endif

    <form method="POST" id="create_edit_contacts_list"
        action="{{ $contacts_list->id ? route('admin.spaces.contacts_lists.update', [$space, $contacts_list->id]) : route('admin.spaces.contacts_lists.store', $space) }}"
        accept-charset="UTF-8">
        @csrf
        @method($contacts_list->id ? 'put' : 'post')
        <div>
            <input placeholder="{{ __('Name') }}    " required="required" name="title" type="text"
                value="{{ $contacts_list->title ?? old('title') }}">
            <label for="username">{{ __('Name') }}</label>
            @include('parts.errors', ['name' => 'title'])
        </div>

        <div>
            <textarea placeholder="{{ __('Description') }}" name="description">{{ $contacts_list->description ?? old('description') }}</textarea>
            <label for="description">{{ __('Description') }}</label>
            @include('parts.errors', ['name' => 'description'])
        </div>
    </form>

    @if ($contacts_list->id)
        <hr>

        <form  method="POST"
            action="{{ route('admin.spaces.contacts_lists.contacts.destroy', [$space, $contacts_list->id]) }}"
            name="contacts_lists_contacts_destroy" accept-charset="UTF-8">
            @csrf
            @method('delete')

            <select name="contacts_ids[]" class="list_toggle" data-list-id="d{{ $contacts_list->id }}"></select>
            <input type="hidden" name="contacts_list_id" value="{{ $contacts_list->id }}">
        </form>

        <form class="inline" method="POST" action="{{ route('admin.spaces.contacts_lists.search', [$space, $contacts_list->id]) }}"
            name="contacts_lists_contacts_search" accept-charset="UTF-8">
            @csrf

            <div class="search">
                <input placeholder="Search by username: +1234, foo_bar…" name="search" type="text"
                    value="{{ request()->get('search', '') }}">
                <label for="search">{{ __('Search') }}</label>
            </div>
            <div class="large">
                <button type="submit" class="btn" title="{{ __('Search') }}"><i class="ph ph-magnifying-glass"></i></button>
                <a href="{{ route('admin.spaces.contacts_lists.edit', [$space, $contacts_list->id]) }}" type="reset"
                    class="btn secondary">{{ __('Reset') }}</a>
            </div>

            <div>
                <a class="btn secondary oppose" href="{{ route('admin.spaces.contacts_lists.contacts.add', [$space, $contacts_list->id]) }}">
                    <i class="ph ph-plus"></i> {{ __('Add contacts') }}
                </a>
            </div>
        </form>

        <table class="large">
            <thead>
                <tr>
                    <th style="width:1%;">
                        <input type="checkbox" onchange="Utils.toggleAll(this)">
                    </th>
                    <th>
                        {{ __('Username') }}
                        <a class="btn small tertiary oppose"
                            onclick="Utils.clearStorageList('d{{ $contacts_list->id }}'); document.querySelector('form[name=contacts_lists_contacts_destroy]').submit()">
                            <i class="ph ph-trash"></i>
                            {{ __('Remove') }} <span class="list_toggle" data-list-id="d{{ $contacts_list->id }}"></span>
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody>
                @if ($contacts->isEmpty())
                    <tr class="empty">
                        <td colspan="2">{{ __('Empty') }}</td>
                    </tr>
                @endif
                @foreach ($contacts as $contact)
                    <tr>
                        <td>
                            <input class="list_toggle" type="checkbox" data-list-id="d{{ $contacts_list->id }}"
                                data-id="{{ $contact->id }}">
                        </td>
                        <td>{{ $contact->identifier }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection

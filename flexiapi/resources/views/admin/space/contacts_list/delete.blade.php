@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.spaces.integration')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.spaces.contacts_lists.index', $space) }}">{{ __('Contacts Lists') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Delete') }}</li>
@endsection

@section('content')
    @include('admin.space.head')
    @include('admin.space.tabs')

    <header>
        <h1><i class="ph ph-trash"></i> {{ __('Delete') }}</h1>
        <a href="{{ route('admin.spaces.contacts_lists.edit', [$space, $contacts_list->id]) }}" class="btn secondary oppose">{{ __('Cancel') }}</a>
        <input form="delete" class="btn" type="submit" value="{{ __('Delete') }}">
    </header>

    <form id="delete" method="POST" action="{{ route('admin.spaces.contacts_lists.destroy', [$space, $contacts_list->id]) }}" accept-charset="UTF-8">
        @csrf
        @method('delete')

        <div class="large">
            <p>{{ __('You are going to permanently delete the following element. Please confirm your action.') }}<br />
                <h3>{{ $contacts_list->title }}</h3>
            </p>

            <input name="contacts_lists_id" type="hidden" value="{{ $contacts_list->id }}">
        </div>
    </form>
@endsection

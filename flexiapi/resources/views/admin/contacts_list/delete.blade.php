@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.contacts_lists.index') }}">{{ __('Contacts Lists') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Delete') }}</li>
@endsection

@section('content')
    <header>
        <h2><i class="ph">trash</i> {{ __('Delete') }}</h2>
        <a href="{{ route('admin.contacts_lists.edit', $contacts_list->id) }}" class="btn btn-secondary oppose">{{ __('Cancel') }}</a>
        <input form="delete" class="btn" type="submit" value="{{ __('Delete') }}">
    </header>
    <form id="delete" method="POST" action="{{ route('admin.contacts_lists.destroy', $contacts_list->id) }}" accept-charset="UTF-8">
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

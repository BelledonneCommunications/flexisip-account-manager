@php
    $items = [];

    if (auth()->user()->superAdmin) {
        $items[route('admin.spaces.show', $space->id)] = __('Information');
        $items[route('admin.spaces.administration', $space->id)] = __('Administration');
        $items[route('admin.spaces.edit', $space->id)] = __('App Configuration');
        $items[route('admin.spaces.integration', $space->id)] = __('Integration');
        $items[route('admin.spaces.contacts_lists.index', $space->id)] = __('Contacts Lists');
    } else if (auth()->user()->admin) {
        $items[route('admin.spaces.me')] = __('Information');
        $items[route('admin.spaces.integration', $space->id)] = __('Integration');
        $items[route('admin.spaces.contacts_lists.index', $space->id)] = __('Contacts Lists');
    }

    $items[route('admin.spaces.configuration', $space->id)] = __('Configuration');
@endphp

@include('parts.tabs', [
    'items' => $items
])
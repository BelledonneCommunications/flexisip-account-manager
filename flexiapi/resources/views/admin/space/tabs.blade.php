@php
    $items = [];

    if (auth()->user()->superAdmin) {
        $items[route('admin.spaces.show', $space->domain)] = __('Information');
        $items[route('admin.spaces.administration', $space->domain)] = __('Administration');
        $items[route('admin.spaces.edit', $space->domain)] = __('App Configuration');
        $items[route('admin.spaces.integration', $space->domain)] = __('Integration');
        $items[route('admin.spaces.contacts_lists.index', $space->domain)] = __('Contacts Lists');
    } else if (auth()->user()->admin) {
        $items[route('admin.spaces.me')] = __('Information');
        $items[route('admin.spaces.integration', $space->domain)] = __('Integration');
        $items[route('admin.spaces.contacts_lists.index', $space->domain)] = __('Contacts Lists');
    }

    $items[route('admin.spaces.configuration', $space->domain)] = __('Configuration');
@endphp

@include('parts.tabs', [
    'items' => $items
])
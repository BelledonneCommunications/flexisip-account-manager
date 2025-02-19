@php
    $items = [];

    if (auth()->user()->superAdmin) {
        $items[route('admin.spaces.show', $space->id)] = __('Information');
        $items[route('admin.spaces.administration', $space->id)] = __('Administration');
        $items[route('admin.spaces.edit', $space->id)] = __('App Configuration');
    } else if (auth()->user()->admin) {
        $items[route('admin.spaces.me')] = __('Information');
    }

    $items[route('admin.spaces.configuration', $space->id)] = __('Configuration');
@endphp

@include('parts.tabs', [
    'items' => $items
])
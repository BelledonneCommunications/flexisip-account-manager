@php
    $items = [];

    if (auth()->user()->superAdmin) {
        $items[route('admin.spaces.show', $space->id)] = 'Information';
        $items[route('admin.spaces.administration', $space->id)] = 'Space Administration';
        $items[route('admin.spaces.edit', $space->id)] = 'App Configuration';
    } else if (auth()->user()->admin) {
        $items[route('admin.spaces.me')] = 'Information';
    }

    $items[route('admin.spaces.configuration', $space->id)] = 'Space Configuration';
@endphp

@include('parts.tabs', [
    'items' => $items
])
@php
    $items = [];

    if (auth()->user()->superAdmin) {
        $items[route('admin.spaces.show', $space->id)] = 'Information';
        $items[route('admin.spaces.edit', $space->id)] = 'Configuration';
        $items[route('admin.spaces.parameters', $space->id)] = 'Parameters';
    }
@endphp

@include('parts.tabs', [
    'items' => $items
])
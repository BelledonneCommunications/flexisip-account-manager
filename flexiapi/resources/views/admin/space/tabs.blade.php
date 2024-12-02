@php
    $items = [
        route('admin.spaces.show', $space->id) => 'Information',
        route('admin.spaces.edit', $space->id) => 'Configuration'
    ];

    if (auth()->user()->superAdmin) {
        $items[route('admin.spaces.parameters', $space->id)] = 'Parameters';
    }
@endphp

@include('parts.tabs', [
    'items' => $items
])
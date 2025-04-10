@php
    $items = [
        route('admin.api_keys.index') => __('API Keys')
    ];

    if (auth()->user()->superAdmin) {
        $items[route('admin.phone_countries.index')] = __('Phone Countries');
    }
@endphp

@include('parts.tabs', [
    'items' => $items
])
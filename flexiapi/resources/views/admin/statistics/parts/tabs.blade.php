@include('parts.tabs', [
    'items' => [
        route('admin.statistics.show', ['type' => 'accounts']) => __('Users'),
        route('admin.statistics.show', ['type' => 'calls']) => __('Calls'),
        route('admin.statistics.show_call_logs') => __('Calls logs'),
        route('admin.statistics.show', ['type' => 'messages']) => __('Messages'),
    ],
])

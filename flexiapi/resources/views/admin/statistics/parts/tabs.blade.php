@include('parts.tabs', [
    'items' => [
        route('admin.statistics.show', ['type' => 'accounts']) => 'Accounts',
        route('admin.statistics.show', ['type' => 'calls']) => 'Calls',
        route('admin.statistics.show_call_logs') => 'Call Logs',
        route('admin.statistics.show', ['type' => 'messages']) => 'Messages',
    ],
])

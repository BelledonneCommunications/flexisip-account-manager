@include('parts.tabs', [
    'items' => [
        route('admin.account.edit', $account->id) => 'Information',
        route('admin.account.statistics.show_call_logs', $account->id) => 'Call Logs',
        route('admin.account.device.index', $account->id) => 'Devices',
        route('admin.account.statistics.show', $account->id) => 'Statistics',
        route('admin.account.activity.index', $account->id) => 'Activity',
        route('admin.account.dictionary.index', $account->id) => 'Dictionary',
    ],
])
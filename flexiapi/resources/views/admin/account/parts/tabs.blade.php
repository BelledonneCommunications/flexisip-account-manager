@include('parts.tabs', [
    'items' => [
        route('admin.account.edit', $account->id) => __('Information'),
        route('admin.account.statistics.show_call_logs', $account->id) => __('Calls logs'),
        route('admin.account.device.index', $account->id) => __('Devices'),
        route('admin.account.statistics.show', $account->id) => __('Statistics'),
        route('admin.account.activity.index', $account->id) => __('Activity'),
        route('admin.account.dictionary.index', $account->id) => __('Dictionary'),
    ],
])
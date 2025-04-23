@include('parts.tabs', [
    'items' => [
        route('admin.account.show', $account) => __('Information'),
        route('admin.account.contact.index', $account) => __('Contacts'),
        route('admin.account.statistics.show_call_logs', $account) => __('Calls logs'),
        route('admin.account.statistics.show', $account) => __('Statistics'),
        route('admin.account.activity.index', $account) => __('Activity'),
    ],
])
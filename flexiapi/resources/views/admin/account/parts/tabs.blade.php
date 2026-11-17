@include('parts.tabs', [
    'items' => [
        route('admin.account.show', $account) => __('Information'),
        route('admin.account.telephony.show', $account) => __('Telephony'),
        route('admin.account.contact.index', $account) => __('Contacts'),
        route('admin.account.statistics.show_call_log', $account) => __('Call log'),
        route('admin.account.statistics.show', $account) => __('Statistics'),
        route('admin.account.activity.index', $account) => __('Activity'),
    ],
])
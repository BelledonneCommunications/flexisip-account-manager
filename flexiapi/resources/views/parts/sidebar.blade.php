<nav>
@php
    $items = [];

    if (auth()->user() && auth()->user()->admin) {
        if (auth()->user()->superAdmin) {
            $items['admin.spaces.index'] = ['title' => __('Spaces'), 'icon' => 'globe-hemisphere-west'];
        } elseif (auth()->user()->admin) {
            $items['admin.spaces.me'] = ['title' => __('My Space'), 'icon' => 'globe-hemisphere-west'];
        }

        $items['admin.account.index'] = ['title' => __('Users'), 'icon' => 'users'];
        $items['admin.statistics.show'] = ['title' => __('Statistics'), 'icon' => 'chart-donut'];
        $items['admin.api_keys.index'] = ['title' => __('Settings'), 'icon' => 'gear'];
    }
@endphp

@include('parts.sidebar_items', ['items' => $items])

<hr />

@php
    $items = [];
    $items['account.telephony'] = ['title' => __('Telephony'), 'icon' => 'phone'];
@endphp

@include('parts.sidebar_items', ['items' => $items])

</nav>
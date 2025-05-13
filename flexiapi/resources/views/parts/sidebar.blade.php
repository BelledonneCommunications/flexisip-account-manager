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
        $items['admin.contacts_lists.index'] = ['title' => __('Contacts Lists'), 'icon' => 'user-rectangle'];
        $items['admin.statistics.show'] = ['title' => __('Statistics'), 'icon' => 'chart-donut'];
        $items['admin.api_keys.index'] = ['title' => __('Settings'), 'icon' => 'gear'];
    }
@endphp

@foreach ($items as $route => $value)
    <a @if (str_starts_with(url()->current(), route($route)))class="current"@endif href="{{ route($route) }}">
        <i class="ph">{{ $value['icon'] }}</i>
        {{ $value['title'] }}
    </a>
@endforeach
</nav>
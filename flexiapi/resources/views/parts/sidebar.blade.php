<nav>
@php
    $items = [];

    if (auth()->user() && auth()->user()->admin) {
        if (auth()->user()->superAdmin) {
            $items['admin.spaces.index'] = ['title' => 'Spaces', 'icon' => 'globe-hemisphere-west'];
            $items['admin.phone_countries.index'] = ['title' => 'Phone Countries', 'icon' => 'flag'];
        } elseif (auth()->user()->admin) {
            $items['admin.spaces.me'] = ['title' => 'My Space', 'icon' => 'globe-hemisphere-west'];
        }

        $items['admin.account.index'] = ['title' => 'Accounts', 'icon' => 'users'];
        $items['admin.contacts_lists.index'] = ['title' => 'Contacts Lists', 'icon' => 'user-rectangle'];
        $items['admin.statistics.show'] = ['title' => 'Statistics', 'icon' => 'chart-donut'];
    }
@endphp

@foreach ($items as $route => $value)
    <a @if (str_starts_with(url()->current(), route($route)))class="current"@endif href="{{ route($route) }}">
        <i class="ph">{{ $value['icon'] }}</i>
        {{ $value['title'] }}
    </a>
@endforeach
</nav>
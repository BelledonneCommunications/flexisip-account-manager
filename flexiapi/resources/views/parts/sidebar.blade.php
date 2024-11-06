<nav>
@php
    $items = [
        'account.dashboard' => ['title' => 'Dashboard', 'icon' => 'gauge'],
    ];

    if (auth()->user() && auth()->user()->admin) {
        $items['admin.account.index'] = ['title' => 'Accounts', 'icon' => 'users'];
        $items['admin.contacts_lists.index'] = ['title' => 'Contacts Lists', 'icon' => 'user-rectangle'];
        $items['admin.statistics.show'] = ['title' => 'Statistics', 'icon' => 'chart-donut'];

        if (auth()->user()->superAdmin) {
            $items['admin.sip_domains.index'] = ['title' => 'SIP Domains', 'icon' => 'hard-drivers'];
            $items['admin.phone_countries.index'] = ['title' => 'Phone Countries', 'icon' => 'flag'];
        }
    }
@endphp

@foreach ($items as $route => $value)
    <a @if (str_starts_with(url()->current(), route($route)))class="current"@endif href="{{ route($route) }}">
        <i class="ph">{{ $value['icon'] }}</i>
        {{ $value['title'] }}
    </a>
@endforeach
</nav>
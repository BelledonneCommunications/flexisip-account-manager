<nav>
@php
    $items = [
        'account.dashboard' => ['title' => 'Dashboard', 'icon' => 'dashboard'],
    ];

    if (auth()->user() && auth()->user()->admin) {
        $items['admin.account.index'] = ['title' => 'Accounts', 'icon' => 'people'];
        $items['admin.contacts_lists.index'] = ['title' => 'Contacts Lists', 'icon' => 'account_box'];
        $items['admin.statistics.show'] = ['title' => 'Statistics', 'icon' => 'analytics'];
    }
@endphp

@foreach ($items as $route => $value)
    <a @if (str_starts_with(url()->current(), route($route)))class="current"@endif href="{{ route($route) }}">
        <i class="material-icons-outlined">{{ $value['icon'] }}</i>
        {{ $value['title'] }}
    </a>
@endforeach
</nav>
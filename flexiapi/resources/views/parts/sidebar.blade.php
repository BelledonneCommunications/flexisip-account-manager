<nav>
@foreach ([
    'account.dashboard' => ['title' => 'Dashboard', 'icon' => 'dashboard'],
    'admin.account.index' => ['title' => 'Accounts', 'icon' => 'people'],
    'admin.statistics.show.day' => ['title' => 'Statistics', 'icon' => 'analytics'],
] as $route => $value)
    <a @if (str_starts_with(url()->current(), route($route)))class="current"@endif href="{{ route($route) }}">
        <i class="material-icons">{{ $value['icon'] }}</i>
        {{ $value['title'] }}
    </a>
@endforeach
</nav>
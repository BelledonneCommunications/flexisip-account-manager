<ul class="tabs">
    @foreach ($items as $route => $title)
        <li @if (url()->current() == route($route))class="current"@endif><a href="{{ route($route) }}">{{ $title }}</a></li>
    @endforeach
</ul>

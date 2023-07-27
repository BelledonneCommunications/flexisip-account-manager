<ul class="tabs">
    @foreach ($items as $route => $title)
        <li @if (url()->current() == $route)class="current"@endif><a href="{{ $route }}">{{ $title }}</a></li>
    @endforeach
</ul>

@foreach ($items as $route => $value)
    <a @if (str_starts_with(url()->current(), route($route)))class="current"@endif href="{{ route($route) }}">
        <i class="ph ph-{{ $value['icon'] }}"></i>
        {{ $value['title'] }}
    </a>
@endforeach
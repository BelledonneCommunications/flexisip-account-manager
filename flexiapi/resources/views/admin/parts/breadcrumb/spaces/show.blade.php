@include('admin.parts.breadcrumb.spaces.index')
<li class="breadcrumb-item">
    @if (auth()->user()->superAdmin)
        <a href="{{ route('admin.spaces.show', $space) }}">{{ $space->name }}</a>
    @else
        {{ $space->name }}
    @endif
</li>
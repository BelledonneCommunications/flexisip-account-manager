@include('admin.parts.breadcrumb.spaces.show')
<li class="breadcrumb-item">
    <a href="{{ route('admin.spaces.integration', $space) }}">{{ __('Integration') }}</a>
</li>
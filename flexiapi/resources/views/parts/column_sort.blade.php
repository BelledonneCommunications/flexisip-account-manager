<th class="line">
    <a
        href="{{ route(request()->route()->getName(), [
            'order_by' => $key,
            'order_sort' => request()->get('order_sort', 'desc') == 'desc' ? 'asc' : 'desc'
            ] + request()->except('_token', 'query')) }}">
        {{ $title }}
        <i class="material-icons-outlined">
            @if (request()->get('order_by') == $key && request()->has('order_sort'))
                @if (request()->get('order_sort') == 'asc')
                    expand_more
                @else
                    expand_less
                @endif
            @else
                sort
            @endif
        </i>
    </a>
</th>

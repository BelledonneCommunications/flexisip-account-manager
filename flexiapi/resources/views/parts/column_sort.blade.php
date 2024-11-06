<th class="line">
    @php
        $uriParams = $uriParams ?? [];
    @endphp

    <a
        href="{{ route(request()->route()->getName(), $uriParams + [
            'order_by' => $key,
            'order_sort' => request()->get('order_sort', 'desc') == 'desc' ? 'asc' : 'desc'
            ] + request()->except('_token', 'query')) }}">
        {{ $title }}
        <i class="ph">
            @if (request()->get('order_by') == $key && request()->has('order_sort'))
                @if (request()->get('order_sort') == 'asc')
                    caret-down
                @else
                    caret-up
                @endif
            @else
                funnel
            @endif
        </i>
    </a>
</th>

<table>
    <thead>
        <tr>
            <th>From</th>
            <th>To</th>
            <th>Time</th>
        </tr>
    </thead>
    <tbody>
        @if ($calls->isEmpty())
            <tr class="empty">
                <td colspan="3">No Calls</td>
            </tr>
        @endif
        @foreach ($calls as $call)
            <tr>
                <td class="line">
                    @if (isset($account) && $account->identifier != $call->from)
                        <b>{{ $call->from }}</b>
                    @else
                        {{ $call->from }}
                    @endif
                </td>
                <td class="line">
                    @if (isset($account) && $account->identifier != $call->to)
                        <b>{{ $call->to }}</b>
                    @else
                        {{ $call->to }}
                    @endif
                </td>
                <td class="line">
                    {{ $call->initiated_at }}
                    @if ($call->ended_at)
                        ({{ $call->ended_at->diffForHumans($call->initiated_at, true) }})
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

{{ $calls->links('pagination::bootstrap-4') }}

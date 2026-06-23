<table>
    <thead>
        <tr>
            <th>{{ __('From') }}</th>
            <th>{{ __('To') }}</th>
            <th><i class="ph ph-clock"></i></th>
            <th>{{ __('State') }}</th>
        </tr>
    </thead>
    <tbody>
        @if ($calls->isEmpty())
            <tr class="empty">
                <td colspan="4">{{ __('Empty') }}</td>
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
                <td class="line" title="{{ $call->initiated_at }}">
                    @if ($call->ended_at)
                        {{ $call->ended_at->diffForHumans($call->initiated_at, true) }}

                        -
                    @endif

                    {{ $call->initiated_at->diffForHumans() }}
                </td>
                <td class="{{ $call->state->cssClass() }}">
                    <i class="ph ph-{{ $call->state->icon() }}"></i> {{ $call->state->label() }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

{{ $calls->links('pagination::bootstrap-4') }}

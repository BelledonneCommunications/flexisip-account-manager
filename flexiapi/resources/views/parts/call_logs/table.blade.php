<table>
    <thead>
        <tr>
            <th style="width: 1%"><i class="ph ph-arrows-out-simple"></i></th>
            @if (isset($admin_view) && $admin_view)
                <th>{{ __('From') }}</th>
                <th>{{ __('To') }}</th>
            @else
                <th>{{ __('Contact') }}</th>
            @endif
            <th>{{ __('Length') }}</th>
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
                <td>
                    @if (isset($account) )
                        @if ($account->identifier != $call->to)
                            <i title="{{ __('Outgoing call') }}" class="{{ $call->state->cssClass() }} ph ph-arrow-up-right"></i>
                        @else
                            <i title="{{ __('Incoming call') }}" class="{{ $call->state->cssClass() }} ph ph-arrow-down-left"></i>
                        @endif
                    @endif
                </td>
                @if (isset($admin_view) && $admin_view)
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
                @else
                    <td class="line">
                        @if (isset($account) && $account->identifier != $call->to)
                            {{ $call->to }}
                        @else
                            {{ $call->from }}
                        @endif
                    </td>
                @endif
                <td class="line" >
                    @if ($call->ended_at)
                        {{ $call->ended_at->diffForHumans($call->initiated_at, true) }}
                    @endif
                </td>
                <td class="line" title="{{ $call->initiated_at }}">
                    {{ $call->initiated_at->diffForHumans() }}
                </td>
                <td class="{{ $call->state->cssClass() }}">
                    <i class="ph ph-{{ $call->state->icon() }}"></i> {{ $call->state->label() }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

{{ $calls->appends(request()->only('direction', 'from', 'to', 'contacts_list', 'domain'))->links('pagination::bootstrap-4') }}

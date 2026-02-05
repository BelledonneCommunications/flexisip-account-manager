<h3>
    {{ __('Call Forwarding') }}
</h3>

<form id="edit" method="POST" action="{{ route('admin.account.call_forwardings.update', $account->id) }}" accept-charset="UTF-8">
    @csrf
    @method('put')
    @php($callForwardings = $account->callForwardingsDefault)

    <section>
        <h4>{{ __('All the calls') }}</h4>

        <div class="checkbox">
            <input id="always[enabled]" type="checkbox" @if ($callForwardings['always']->enabled) checked @endif name="always[enabled]"
            onchange="if (this.checked) { setCheckboxValue('away[enabled]', false); setCheckboxValue('busy[enabled]', false); }">
            <label for="always[enabled]"></label>
        </div>

        @include('admin.account.call_forwardings.edit_select_part', ['callForwarding' => $callForwardings, 'type' => 'always'])
    </section>

    <section>
        <h4>{{ __('No answer') }}</h4>

        <div class="checkbox">
            <input id="away[enabled]" type="checkbox" @if ($callForwardings['away']->enabled) checked @endif name="away[enabled]"
            onchange="if (this.checked) { setCheckboxValue('always[enabled]', false); }">
            <label for="away[enabled]"></label>
        </div>

        @include('admin.account.call_forwardings.edit_select_part', ['callForwarding' => $callForwardings, 'type' => 'away'])
    </section>

    <section>
        <h4>{{ __('Line occupied') }}</h4>

        <div class="checkbox">
            <input id="busy[enabled]" type="checkbox" @if ($callForwardings['busy']->enabled) checked @endif name="busy[enabled]"
            onchange="if (this.checked) { setCheckboxValue('always[enabled]', false); }">
            <label for="busy[enabled]"></label>
        </div>

        @include('admin.account.call_forwardings.edit_select_part', ['callForwarding' => $callForwardings, 'type' => 'busy'])
    </section>

    <div class="large">
        <input class="btn small oppose" type="submit" value="{{ __('Update') }}">
    </div>
</form>
<h3>
    {{ __('Call Forwarding') }}
</h3>

<dialog id="always_dialog" closedby="any">
    <h2>{{ __('Activate call forwarding for all calls?') }}</h2>
    <p>{{ __('Forwarding all calls takes priority over the other rules. If you activate this option all the other rules will be disabled.') }}</p>

    <button class="btn small oppose" commandfor="always_dialog" onclick="
        setCheckboxValue('always[enabled]', true);
        setCheckboxValue('away[enabled]', false);
        setCheckboxValue('busy[enabled]', false);
        document.querySelector('#always_dialog').close()">{{ __('Activate') }}</button>
    <button class="btn small oppose secondary" commandfor="always_dialog" command="close" onclick="document.querySelector('#always_dialog').close()">{{ __('Cancel') }}</button>
</dialog>

<dialog id="away_dialog" closedby="any">
    <h2>{{ __('Activate conditional call forwarding?') }}</h2>
    <p>{{ __('Activating conditional call forwarding will automatically disable the Forward All Calls rule.') }}</p>

    <button class="btn small oppose" commandfor="away_dialog" onclick="
        setCheckboxValue('always[enabled]', false);
        setCheckboxValue('away[enabled]', true);
        document.querySelector('#away_dialog').close()">{{ __('Activate') }}</button>
    <button class="btn small oppose secondary" commandfor="away_dialog" command="close" onclick="document.querySelector('#away_dialog').close()">{{ __('Cancel') }}</button>
</dialog>

<dialog id="busy_dialog" closedby="any">
    <h2>{{ __('Activate conditional call forwarding?') }}</h2>
    <p>{{ __('Activating conditional call forwarding will automatically disable the Forward All Calls rule.') }}</p>

    <button class="btn small oppose" commandfor="busy_dialog" onclick="
        setCheckboxValue('always[enabled]', false);
        setCheckboxValue('busy[enabled]', true);
        document.querySelector('#busy_dialog').close()">{{ __('Activate') }}</button>
    <button class="btn small oppose secondary" commandfor="busy_dialog" command="close" onclick="document.querySelector('#busy_dialog').close()">{{ __('Cancel') }}</button>
</dialog>

<form id="edit" method="POST" action="@if ($account->admin) {{ route('admin.account.call_forwardings.update', $account->id) }}@else{{ route('account.call_forwardings.update') }}@endif" accept-charset="UTF-8">
    @csrf
    @method('put')
    @php($callForwardings = $account->callForwardingsDefault)

    <section class="block">
        <div>
            <h4>{{ __('All the calls') }}</h4>
            <i class="ph ph-info tooltip" title="{{ __('All incoming calls are forwarded, whether you answer, decline the call or are already on a call.') }}"></i>
        </div>

        <div class="checkbox">
            <span class="badge">{{ __('Priority rule') }}</span>
            <input id="always[enabled]" type="checkbox" @if ($callForwardings['always']->enabled) checked @endif name="always[enabled]"
            onchange="if (this.checked
                && (document.querySelector('#away\\[enabled\\]').checked || document.querySelector('#busy\\[enabled\\]').checked)) {
                    setCheckboxValue('always[enabled]', false);
                    document.querySelector('#always_dialog').showModal();
                }">
            <label for="always[enabled]"></label>
        </div>

        @include('account.call_forwardings.edit_select_part', ['callForwarding' => $callForwardings, 'type' => 'always'])
    </section>

    <span class="line large">{{ __('Or') }}</span>

    <section class="block">
        <div>
            <h4>@if ($account->admin){{ __('No answer') }}@else{{ __('If no one is answering') }}@endif</h4>
            <i class="ph ph-info tooltip" title="{{ __('Calls are only forwarded when your line is busy with another call.') }}"></i>
        </div>

        <div class="checkbox">
            <input id="away[enabled]" type="checkbox" @if ($callForwardings['away']->enabled) checked @endif name="away[enabled]"
            onchange="if (this.checked
                && document.querySelector('#always\\[enabled\\]').checked) {
                    setCheckboxValue('away[enabled]', false);
                    document.querySelector('#away_dialog').showModal();
                }">
            <label for="away[enabled]"></label>
        </div>

        @include('account.call_forwardings.edit_select_part', ['callForwarding' => $callForwardings, 'type' => 'away'])
    </section>

    <section class="block">
        <div>
            <h4>@if ($account->admin){{ __('Busy line') }}@else{{ __('If the line is busy') }}@endif</h4>
            <i class="ph ph-info tooltip" title="{{ __('Calls are only forwarded if you do not answer or if you decline the call.') }}"></i>
        </div>

        <div class="checkbox">
            <input id="busy[enabled]" type="checkbox" @if ($callForwardings['busy']->enabled) checked @endif name="busy[enabled]"
            onchange="if (this.checked
                && document.querySelector('#always\\[enabled\\]').checked) {
                    setCheckboxValue('busy[enabled]', false);
                    document.querySelector('#busy_dialog').showModal();
                }">
            <label for="busy[enabled]"></label>
        </div>

        @include('account.call_forwardings.edit_select_part', ['callForwarding' => $callForwardings, 'type' => 'busy'])
    </section>

    <div class="large">
        <input class="btn small oppose" type="submit" value="{{ __('Update') }}">
    </div>
</form>
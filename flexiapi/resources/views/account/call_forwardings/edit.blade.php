<h3>
    @if (auth()->user()->admin){{ __('Call Forwarding') }}@else{{ __('My Call Forwarding') }}@endif
</h3>

<form id="edit" method="POST" action="@if (auth()->user()->admin) {{ route('admin.account.call_forwardings.update', $account->id) }}@else{{ route('account.call_forwardings.update') }}@endif" accept-charset="UTF-8">
    @csrf
    @method('put')
    @php($callForwardings = $account->callForwardingsDefault)
    @php($contacts = resolveUserContacts($account)->get())
    @php($status = old('status') ?? ($callForwardings['always']->enabled ? 'always' : ($callForwardings['no_answer']->enabled ? 'no_answer' : 'disabled')))

    <section class="block">
        <div>
            <h4>{{ __('Disabled') }}</h4>
        </div>
        <div class="radio">
            <input name="status" value="disabled" type="radio" @if ($status == 'disabled') checked @endif>
        </div>
    </section>

    <section class="block">
        <div>
            <h4>{{ __('All the calls') }}</h4>
            <i class="ph ph-info tooltip">
                <span class="tooltiptext">
                    {{ __('All incoming calls are forwarded directly, without making your devices ring.') }}
                </span>
            </i>
        </div>

        <div class="radio">
            <input name="status" value="always" type="radio" @if ($status == 'always') checked @endif>
        </div>

        <div class="select" data-value="{{ $callForwardings['always']->forward_to ?? 'sip_uri' }}">
            <select name="always[forward_to]" onchange="this.parentNode.dataset.value = this.value">
                <option @if ($callForwardings['always']->forward_to == 'sip_uri') selected @endif value="sip_uri">{{ __('SIP Adress') }}</option>
                <option @if ($callForwardings['always']->forward_to == 'voicemail') selected @endif value="voicemail" @if(!$account->space->voicemailAccount) disabled @endif>{{ __('Voicemails') }}</option>
            </select>
            <label for="always[forward_to]">{{ __('Destination') }}</label>
        </div>
        <div class="togglable sip_uri">
            @include('account.call_forwardings.sip_uri_field', ['type' => 'always'])
        </div>
        <div class="togglable voicemail"></div>
    </section>

    <section class="block">
        <div>
            <h4>{{ __('No answer or busy') }}</h4>
            <i class="ph ph-info tooltip">
                <span class="tooltiptext">{{ __('Calls are forwarded if you are already on the line, if you do not answer before the ringing time expires, or if you reject the call.') }}</span>
            </i>
        </div>

        <div class="radio">
            <input name="status" value="no_answer" type="radio" @if ($status == 'no_answer') checked @endif>
        </div>

        <div class="select" data-value="{{ $callForwardings['no_answer']->forward_to ?? 'sip_uri' }}">
            <select name="no_answer[forward_to]" onchange="this.parentNode.dataset.value = this.value">
                <option @if ($callForwardings['no_answer']->forward_to == 'sip_uri') selected @endif value="sip_uri">{{ __('SIP Adress') }}</option>
            </select>
            <label for="no_answer[forward_to]">{{ __('Destination') }}</label>
        </div>
        <div class="togglable sip_uri">
            @include('account.call_forwardings.sip_uri_field', ['type' => 'no_answer'])
        </div>
    </section>

    <div class="large">
        <input class="btn small oppose" type="submit" value="{{ __('Update') }}">
    </div>
</form>

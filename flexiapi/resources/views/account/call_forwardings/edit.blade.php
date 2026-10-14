<h3>
    @if (auth()->user()->admin){{ __('Call Forwarding') }}@else{{ __('My Call Forwarding') }}@endif
</h3>

<form id="edit" method="POST" action="@if (auth()->user()->admin) {{ route('admin.account.call_forwardings.update', $account->id) }}@else{{ route('account.call_forwardings.update') }}@endif" accept-charset="UTF-8">
    @csrf
    @method('put')
    @php($callForwardings = $account->callForwardingsDefault)
    @php($contacts = resolveUserContacts($account)->get())

    <section class="block">
        <div>
            <h4>{{ __('Disabled') }}</h4>
        </div>
        <div class="radio">
            <input name="status" value="disabled" type="radio" checked>
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
            <input name="status" value="always" type="radio" @if (old('status') == 'always' || $callForwardings['always']->enabled) checked @endif>
        </div>

        <div class="select" data-value="{{ $callForwardings['always']->forward_to ?? 'sip_uri' }}">
            <select name="always[forward_to]" onchange="this.parentNode.dataset.value = this.value">
                <option @if ($callForwardings['always']->forward_to == 'sip_uri') selected @endif value="sip_uri">{{ __('SIP Adress') }}</option>
                <option @if ($callForwardings['always']->forward_to == 'voicemail') selected @endif value="voicemail" @if(!$account->space->voicemailAccount) disabled @endif>{{ __('Voicemails') }}</option>
                <option @if ($callForwardings['always']->forward_to == 'contact') selected @endif @if($contacts->isEmpty()) disabled @endif value="contact">{{ __('Contact') }}</option>
            </select>
            <label for="always[forward_to]">{{ __('Destination') }}</label>
        </div>
        <div class="togglable sip_uri">
            <input placeholder="sip:username@server.com" name="always[sip_uri]" type="text" id="always[sip_uri]" value="{{ old('always.sip_uri') ?? $callForwardings['always']->sipAccount?->sip_uri }}">
            <label for="sip">{{ __('SIP Adress') }}</label>
            @include('parts.errors', ['name' => 'always.sip_uri'])
        </div>
        <div class="togglable voicemail"></div>
        <div class="select togglable contact">
            <select name="always[contact_account_id]">
                @foreach ($contacts as $contact)
                    <option @if ($callForwardings['always']->contact_account_id == $contact->id) selected @endif value="{{ $contact->id }}">sip:{{ $contact->identifier }}</option>
                @endforeach
            </select>
            <label for="contact">{{ __('Contact') }}</label>
            @include('parts.errors', ['name' => 'always.contact_account_id'])
        </div>
    </section>

    <section class="block">
        <div>
            <h4>{{ __('No answer or busy') }}</h4>
            <i class="ph ph-info tooltip">
                <span class="tooltiptext">{{ __('Calls are forwarded if you are already on the line, if you do not answer before the ringing time expires, or if you reject the call.') }}</span>
            </i>
        </div>

        <div class="radio">
            <input name="status" value="no_answer" type="radio" @if (old('status') == 'no_answer' || $callForwardings['no_answer']->enabled)checked @endif>
        </div>

        <div class="select" data-value="{{ $callForwardings['no_answer']->forward_to ?? 'sip_uri' }}">
            <select name="no_answer[forward_to]" onchange="this.parentNode.dataset.value = this.value">
                <option @if ($callForwardings['no_answer']->forward_to == null || $callForwardings['no_answer']->forward_to == 'sip_uri') selected @endif value="sip_uri">{{ __('SIP Adress') }}</option>
                <option @if ($callForwardings['no_answer']->forward_to == 'contact') selected @endif @if($contacts->isEmpty()) disabled @endif value="contact">{{ __('Contact') }}</option>
            </select>
            <label for="no_answer[forward_to]">{{ __('Destination') }}</label>
        </div>
        <div class="togglable sip_uri">
            <input placeholder="sip:username@server.com" name="no_answer[sip_uri]" type="text" id="no_answer[sip_uri]" value="{{ old('no_answer.sip_uri') ?? $callForwardings['no_answer']->sipAccount?->sip_uri }}">
            <label for="sip">{{ __('SIP Adress') }}</label>
            @include('parts.errors', ['name' => 'no_answer.sip_uri'])
        </div>
        <div class="select togglable contact">
            <select name="no_answer[contact_account_id]">
                @foreach ($contacts as $contact)
                    <option @if ($callForwardings['no_answer']->contact_account_id == $contact->id) selected @endif value="{{ $contact->id }}">sip:{{ $contact->identifier }}</option>
                @endforeach
            </select>
            <label for="contact">{{ __('Contact') }}</label>
            @include('parts.errors', ['name' => 'no_answer.contact_account_id'])
        </div>
    </section>

    <div class="large">
        <input class="btn small oppose" type="submit" value="{{ __('Update') }}">
    </div>
</form>
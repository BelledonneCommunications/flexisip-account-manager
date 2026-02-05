<div class="select" data-value="{{ $callForwardings[$type]->forward_to }}">
    <select name="{{ $type }}[forward_to]" onchange="this.parentNode.dataset.value = this.value">
        <option @if ($callForwardings[$type]->forward_to == 'voicemail') selected @endif value="voicemail">{{ __('Voicemails') }}</option>
        <option @if ($callForwardings[$type]->forward_to == null || $callForwardings[$type]->forward_to == 'sip_uri') selected @endif value="sip_uri">{{ __('SIP Adress') }}</option>
        <option @if ($callForwardings[$type]->forward_to == 'contact') selected @endif value="contact">{{ __('Contact') }}</option>
    </select>
    <label for="{{ $type }}[forward_to]">{{ __('Destination') }}</label>
</div>
<div class="togglable sip_uri">
    <input placeholder="sip:username@server.com" list="contacts" name="{{ $type }}[sip_uri]" type="text" id="busy[sip_uri]" value="{{ $callForwardings[$type]->sip_uri }}">
    <label for="sip">{{ __('SIP Adress') }}</label>
    @include('parts.errors', ['name' => $type . '.sip_uri'])
</div>
<div class="togglable voicemail"></div>
<div class="select togglable contact">
    <select name="{{ $type }}[contact_id]">
        @foreach (resolveUserContacts($account)->get() as $contact)
            <option @if ($callForwardings[$type]->contact_id == $contact->id) selected @endif value="{{ $contact->id }}">{{ $contact->identifier }}</option>
        @endforeach
    </select>
    <label for="contact">{{ __('Contact') }}</label>
    @include('parts.errors', ['name' => $type . '.contact'])
</div>
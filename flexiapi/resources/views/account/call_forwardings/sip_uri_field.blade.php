<div class="sip_uri_field">
    <datalist id="contacts">
        @foreach ($contacts as $contact)
            <option value="{{ $contact->identifier }}">{{ $contact->display_name ?: $contact->username }} ({{ $contact->identifier }})</option>
        @endforeach
    </datalist>

    <span class="def">sip:</span>
    <input placeholder="account@server.com" list="contacts" onmouseover="focus(); old = value;"  onmouseup="value = old;" name="{{ $type }}[sip_uri]" type="text" id="{{ $type }}[sip_uri]" value="{{ old($type . '.sip_uri') ?? $callForwardings[$type]->sipAccount?->identifier }}">
</div>
@include('parts.errors', ['name' => $type . '.sip_uri'])

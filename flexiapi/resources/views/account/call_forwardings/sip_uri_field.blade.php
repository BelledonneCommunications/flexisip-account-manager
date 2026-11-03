<div class="sip_uri_field">
    <datalist id="contacts">
        @foreach ($contacts as $contact)
            <option value="sip:{{ $contact->identifier }}">{{ $contact->display_name ?: $contact->identifier }} ({{ $contact->identifier }})</option>
        @endforeach
    </datalist>

    <input placeholder="sip:account@server.com" onmouseover="focus(); old = value;" onmousedown="value = '';" onmouseup="value = old;" list="contacts" name="{{ $type }}[sip_uri]" type="text" id="{{ $type }}[sip_uri]" value="{{ old($type . '.sip_uri') ?? $callForwardings[$type]->sipAccount?->sip_uri }}">
</div>
@include('parts.errors', ['name' => $type . '.sip_uri'])

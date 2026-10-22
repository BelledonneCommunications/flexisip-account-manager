{{--
    Call Forwarding SIP URI field with the contacts picker

    Required parameters:
      - string $type: the forwarding type ('always' or 'no_answer')

    Inherited from the including view:
      - $callForwardings: the account default call forwardings (keyed by type)
      - $contacts: the collection of the account contacts
--}}

<div class="sip_uri_field">
    <input placeholder="sip:account@server.com" name="{{ $type }}[sip_uri]" type="text" id="{{ $type }}[sip_uri]" value="{{ old($type . '.sip_uri') ?? $callForwardings[$type]->sipAccount?->sip_uri }}">
    <button type="button" class="contacts_picker_button" title="{{ __('Pick from the contacts') }}" onclick="toggleContactsPicker(this)">
        <i class="ph ph-address-book"></i>
    </button>
    <label for="sip">{{ __('SIP Adress') }}</label>
    <div class="contacts_picker">
        @if ($contacts->isEmpty())
            <div class="empty">{{ __('No Contact') }}</div>
        @else
            @foreach ($contacts as $contact)
                <button type="button" data-sip="sip:{{ $contact->identifier }}" onclick="pickContact(this)">
                    <span>{{ $contact->display_name ?: $contact->identifier }}</span>
                    <small>sip:{{ $contact->identifier }}</small>
                </button>
            @endforeach
        @endif
    </div>
</div>
@include('parts.errors', ['name' => $type . '.sip_uri'])

@once
    @push('scripts')
        <script type="text/javascript">
            function toggleContactsPicker(button) {
                const picker = button.parentNode.querySelector('.contacts_picker');

                document.querySelectorAll('.contacts_picker.open').forEach(function (open) {
                    if (open !== picker) {
                        open.classList.remove('open');
                    }
                });

                picker.classList.toggle('open');
            }

            function pickContact(button) {
                button.closest('.togglable').querySelector('input[type=text]').value = button.dataset.sip;
                button.closest('.contacts_picker').classList.remove('open');
            }

            document.addEventListener('click', function (event) {
                if (!event.target.closest('.togglable.sip_uri')) {
                    document.querySelectorAll('.contacts_picker.open').forEach(function (open) {
                        open.classList.remove('open');
                    });
                }
            });
        </script>
    @endpush
@endonce

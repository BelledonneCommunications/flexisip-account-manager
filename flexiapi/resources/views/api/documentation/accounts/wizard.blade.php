## Wizard Link

### `POST /wizard`
<span class="badge badge-warning">Admin</span>

Checkout the related <a href="{{ route('wizard.documentation') }}">Wizard documentation</a>.

Generate a one-time wizard link to allow users to connect their Linphone client, initiate a call, and more.

JSON parameters:

* `provisioning_account_id`, ID of the account to authenticate. If omitted, the wizard link will not provision any account.
* `linphone_action`, Action to perform on the Linphone client. Accepted values: `call`, `show`, `bye`, `accept`, `decline`.
* `sip`, SIP address to call. Required when `linphone_action` is `call`.
* `linphone_use_sips`, Whether to use SIPS (secure SIP over TLS) to initiate the call. Defaults to `false`.

This endpoint will return the following JSON:

```
{
    "token": "abc12345",
    "provisioning_account_id": 42,
    "sip": "john@sip.example.org",
    "linphone_action": "call",
    "linphone_use_sips": false,
    "url": "https://{space_host}/wizard/{token}"
}
```

Notes:

- The wizard link is **single-use** and expires after being consumed.
- If `provisioning_account_id` is omitted, the link will still trigger the requested action but will not authenticate or provision any account.
- `sip` is only relevant when `linphone_action` is set to `call`.

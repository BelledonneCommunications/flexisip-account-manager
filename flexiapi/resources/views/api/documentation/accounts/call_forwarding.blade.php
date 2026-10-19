## Account Call Forwardings

When a `call_forwarding` object is configured to forward to a contact (`contact_account_id: 1234`) the SIP URI of the contact is returned in a `contact_sip_uri` attribute, and when it is configured to forward to an account of the Space (`sip_account_id: 1234`) the SIP URI of the account is returned in a `sip_sip_uri` attribute.

```
{
  "id": 1,
  "type": "always",
  "enabled": true,
  "sip_account_id": null,
  "voicemail_account_id": null,
  "contact_account_id": 3,
  "contact_sip_uri": "sip:mcclure.kailey@sip.example.com",
  "sip_sip_uri": null,
  "updated_at": "2026-03-12T14:53:22.000000Z",
  "created_at": "2026-03-12T14:53:22.000000Z"
}
```

### `GET /accounts/{id/me}/call_forwardings`
<span class="badge badge-info">User</span>
<span class="badge badge-warning">Admin</span>

Return the user Call Forwardings.

### `GET /accounts/{id/me}/call_forwardings/{call_forwarding_id}`
<span class="badge badge-info">User</span>
<span class="badge badge-warning">Admin</span>

Return a Call Forwarding configuration.

### `POST /accounts/{id/me}/call_forwardings`
<span class="badge badge-info">User</span>
<span class="badge badge-warning">Admin</span>

Create a new Call Forwarding configuration.

JSON parameters:

* `type` **required**, must be `always` or `no_answer` one of each declaration maximum per Account
* `contact_account_id` must be a valid `contact_id` of the contact, `voicemail` and `sip_account_id` must be null
* `sip_account_id` must be a valid `account_id` of the Space, `contact_account_id` and `voicemail` must be null, cannot designate the account itself
* `voicemail` boolean, `contact_account_id` and `sip_account_id` must be null, cannot be `true` if `type: 'no_answer'`
* one of `voicemail`, `sip_account_id` or `contact_account_id` is **required**: a call forwarding must have a target
* `enabled` **required**, boolean. If `type: always` is enabled `no_answer` must be disabled. If `type: no_answer` is enabled `always` must be disabled.

### `PUT /accounts/{id/me}/call_forwardings/{call_forwarding_id}`
<span class="badge badge-info">User</span>
<span class="badge badge-warning">Admin</span>

Update a Call Forwarding configuration.

JSON parameters:

* `type` **required**, must be `always` or `no_answer` one of each declaration maximum per Account
* `contact_account_id` must be a valid `contact_id` of the contact, `voicemail` and `sip_account_id` must be null
* `sip_account_id` must be a valid `account_id` of the Space, `contact_account_id` and `voicemail` must be null, cannot designate the account itself
* `voicemail` boolean, `contact_account_id` and `sip_account_id` must be null, cannot be `true` if `type: 'no_answer'`
* one of `voicemail`, `sip_account_id` or `contact_account_id` is **required**: a call forwarding must have a target
* `enabled` **required**, boolean. If `type: always` is enabled `no_answer` must be disabled. If `type: no_answer` is enabled `always` must be disabled.

### `DELETE /accounts/{id/me}/call_forwardings/{call_forwarding_id}`
<span class="badge badge-info">User</span>
<span class="badge badge-warning">Admin</span>

Remove a Call Forwarding configuration.

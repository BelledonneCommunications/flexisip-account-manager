## Account Call Forwardings

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

* `type` **required**, must be `always`, `away` or `busy`, one of each declaration maximum per account
* `forward_to` **required**, must be `sip_uri`, `voicemail` or `contact`
* `sip_uri` **required if `forward_to` is set to `sip_uri`**, must be a SIP URI, must be set when `forward_to` set to `sip_uri`
* `contact_id` **required if `forward_to` is set to `contact`**, must be a valid `contact_id` of the contact
* `enabled` **required**, boolean. If `type: always` is enabled `away` and `busy` must be disabled. If `type: away or busy` are enabled `always` must be disabled.

### `PUT /accounts/{id/me}/call_forwardings/{call_forwarding_id}`
<span class="badge badge-info">User</span>
<span class="badge badge-warning">Admin</span>

Create a new Call Forwarding configuration.

JSON parameters:

* `forward_to` **required**, must be `sip_uri`, `voicemail` or `contact`
* `sip_uri` **required if `forward_to` is set to `sip_uri`**, must be a SIP URI, must be set when `forward_to` set to `sip_uri`
* `contact_id` **required if `forward_to` is set to `contact`**, must be a valid `contact_id` of the contact
* `enabled` **required**, boolean. If `type: always` is enabled `away` and `busy` must be disabled. If `type: away or busy` are enabled `always` must be disabled.

### `DELETE /accounts/{id/me}/call_forwardings/{call_forwarding_id}`
<span class="badge badge-info">User</span>
<span class="badge badge-warning">Admin</span>

Remove a Call Forwarding configuration.

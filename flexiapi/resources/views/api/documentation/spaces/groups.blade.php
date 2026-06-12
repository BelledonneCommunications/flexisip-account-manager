## 👾👥 Spaces Groups

### `GET /spaces/{domain}/groups`
<span class="badge badge-warning">Admin</span>

List the groups.

### `GET /spaces/{domain}/groups/{id}`
<span class="badge badge-warning">Admin</span>

Get a specific group.

### `POST /spaces/{domain}/groups`
<span class="badge badge-warning">Admin</span>

Create a group.

JSON parameters:

* `name` **required**, Group name
* `username` **required**, Username of the group's SIP address
* `strategy` **required**, The routing strategy for the group. Must be one of :
    - `ring-all`, 
    - `sequential`, 
    - `round_robin`

### `PATCH /spaces/{domain}/groups/{id}`
<span class="badge badge-warning">Admin</span>

Edit an existing group

* `name`, Group name
* `strategy`, The routing strategy for the group. Must be one of :
    - `ring-all`, 
    - `sequential`, 
    - `round_robin`

### `DELETE /spaces/{domain}/groups/{id}`
<span class="badge badge-warning">Admin</span>

Delete a specific group

### `POST /spaces/{domain}/groups/{id}/accounts/{account_id}`
<span class="badge badge-warning">Admin</span>

Add a specific account to a group

### `DELETE /spaces/{domain}/groups/{id}/accounts/{account_id}`
<span class="badge badge-warning">Admin</span>

Delete a specific account from a group


## 👾👥📤 Spaces External Groups

### `GET /spaces/{domain}/groups/{id}/external`
<span class="badge badge-warning">Admin</span>

Get the external group.

### `POST /spaces/{domain}/groups/{id}/external`
<span class="badge badge-warning">Admin</span>

Create or update the external group.

JSON parameters:

* `username` **required**
* `domain` **required**
* `password` **required**
* `realm` must be different than `domain`
* `registrar` must be different than `domain`
* `outbound_proxy` must be different than `domain`
* `protocol` **required**, must be `UDP`, `TCP` or `TLS`

### `DELETE /spaces/{domain}/groups/{id}/external`
<span class="badge badge-warning">Admin</span>

Delete the external group.

## 👾👥⏩ Spaces Groups Fallback Call Forwarding

### `GET /spaces/{domain}/groups/{id}/fallback_call_forwarding`
<span class="badge badge-info">User</span>
<span class="badge badge-warning">Admin</span>

Return the space group fallback call forwardings.

### `GET /spaces/{domain}/groups/{id}/fallback_call_forwarding`
<span class="badge badge-info">User</span>
<span class="badge badge-warning">Admin</span>

Return a space group call forwarding.

### `POST /spaces/{domain}/groups/{id}/fallback_call_forwarding`
<span class="badge badge-info">User</span>
<span class="badge badge-warning">Admin</span>

Create a new space group call forwarding configuration.

JSON parameters:

* `sip_uri` **required if `forward_to` is set to `sip_uri`**, must be a SIP URI, must be set when `forward_to` set to `sip_uri`

## 👾👥⏩ Spaces Groups Call Forwardings

### `GET /spaces/{domain}/groups/{id}/call_forwardings`
<span class="badge badge-info">User</span>
<span class="badge badge-warning">Admin</span>

Return the space group call forwardings.

### `GET /spaces/{domain}/groups/{id}/call_forwardings/{call_forwarding_id}`
<span class="badge badge-info">User</span>
<span class="badge badge-warning">Admin</span>

Return a space group call forwarding.

### `POST /spaces/{domain}/groups/{id}/call_forwardings`
<span class="badge badge-info">User</span>
<span class="badge badge-warning">Admin</span>

Create a new space group call forwarding configuration.

JSON parameters:

* `sip_uri` **required if `forward_to` is set to `sip_uri`**, must be a SIP URI, must be set when `forward_to` set to `sip_uri`
* `enabled` **required**, boolean.
* `dtmf_number` the number to trigger the call forwarding, only authorized if the group `strategy` is equal to `svi`.
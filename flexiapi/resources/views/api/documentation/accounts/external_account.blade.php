## Account external Account

### `GET /accounts/{id}/external`
<span class="badge badge-warning">Admin</span>

Get the external account.

### `POST /accounts/{id}/external`
<span class="badge badge-warning">Admin</span>

Create or update the external account.

JSON parameters:

* `username` **required**
* `domain` **required**
* `password` **required**
* `realm` must be different than `domain`
* `registrar` must be different than `domain`
* `outbound_proxy` must be different than `domain`
* `protocol` **required**, must be `UDP`, `TCP` or `TLS`

### `DELETE /accounts/{id}/external`
<span class="badge badge-warning">Admin</span>

Delete the external account.
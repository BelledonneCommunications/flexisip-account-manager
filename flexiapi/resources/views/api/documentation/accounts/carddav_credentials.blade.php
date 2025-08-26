## Account CardDav credentials

The following endpoints will return `403 Forbidden` if the requested account doesn't have a DTMF protocol configured.

### `GET /accounts/{id}/carddavs`
<span class="badge badge-warning">Admin</span>

Show an account CardDav servers credentials.

### `GET /accounts/{id}/carddavs/{carddav_id}`
<span class="badge badge-warning">Admin</span>

Show an account CardDav server credentials.

### `PUT /accounts/{id}/carddavs/{carddav_id}`
<span class="badge badge-warning">Admin</span>

Create an account CardDav server credentials.

JSON parameters:

* `username` **required** the username
* `password` **required** the password in plain text
* `algorithm` **required**, values can be `SHA-256` or `MD5`
* `domain` **required** the domain

### `DELETE /accounts/{id}/carddavs/{carddav_id}`
<span class="badge badge-warning">Admin</span>

Delete an account related action.
## Account actions

The following endpoints will return `403 Forbidden` if the requested account doesn't have a DTMF protocol configured.

### `GET /accounts/{id}/actions`
<span class="badge badge-warning">Admin</span>

Show an account related actions.

### `GET /accounts/{id}/actions/{action_id}`
<span class="badge badge-warning">Admin</span>

Show an account related action.

### `POST /accounts/{id}/actions/`
<span class="badge badge-warning">Admin</span>

Create an account action.

JSON parameters:

* `key` **required**, alpha numeric with dashes, lowercase
* `code` **required**, alpha numeric, lowercase

### `PUT /accounts/{id}/actions/{action_id}`
<span class="badge badge-warning">Admin</span>

Create an account action.

JSON parameters:

* `key` **required**, alpha numeric with dashes, lowercase
* `code` **required**, alpha numeric, lowercase

### `DELETE /accounts/{id}/actions/{action_id}`
<span class="badge badge-warning">Admin</span>

Delete an account related action.
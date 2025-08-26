## Account Types

### `GET /account_types`
<span class="badge badge-warning">Admin</span>

Show all the account types.

### `GET /account_types/{id}`
<span class="badge badge-warning">Admin</span>

Show an account type.

### `POST /account_types`
<span class="badge badge-warning">Admin</span>

Create an account type.

JSON parameters:

* `key` **required**, alpha numeric with dashes, lowercase

### `PUT /account_types/{id}`
<span class="badge badge-warning">Admin</span>

Update an account type.

JSON parameters:

* `key` **required**, alpha numeric with dashes, lowercase

### `DELETE /account_types/{id}`
<span class="badge badge-warning">Admin</span>

Delete an account type.

### `POST /accounts/{id}/types/{type_id}`
<span class="badge badge-warning">Admin</span>

Add a type to the account.

### `DELETE /accounts/{id}/contacts/{type_id}`
<span class="badge badge-warning">Admin</span>

Remove a type from the account.
## Account dictionary

### `GET /accounts/{id}/dictionary`
<span class="badge badge-warning">Admin</span>

Get all the account dictionary entries.

### `DELETE /accounts/{id}/dictionary/clear`
<span class="badge badge-warning">Admin</span>

Clear all the account dictionary entries.

### `GET /accounts/{id}/dictionary/{key}`
<span class="badge badge-warning">Admin</span>

Get an account dictionary entry.

### `POST /accounts/{id}/dictionary/{key}`
<span class="badge badge-warning">Admin</span>

Add or update a new entry to the dictionary

JSON parameters:

* `value` **required**, the entry value

### `DELETE /accounts/{id}/dictionary/{key}`
<span class="badge badge-warning">Admin</span>

Delete an account dictionary entry.
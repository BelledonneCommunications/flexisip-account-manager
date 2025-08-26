## Account vCards storage

### `POST /accounts/{id/me}/vcards-storage`
<span class="badge badge-warning">Admin</span>
<span class="badge badge-info">User</span>

Store a vCard.

JSON parameters:

* `vcard`, mandatory, a valid vCard having a mandatory `UID` parameter that is uniquelly identifying it. This `UID` parameter will then be used to manipulate the vcard through the following endpoints as `uuid`.

### `PUT /accounts/{id/me}/vcards-storage/{uuid}`
<span class="badge badge-warning">Admin</span>
<span class="badge badge-info">User</span>

Update a vCard.

JSON parameters:

* `vcard`, mandatory, a valid vCard having a mandatory `UID` parameter that is uniquelly identifying it and is the same as the `uuid` parameter.

### `GET /accounts/{id/me}/vcards-storage`
<span class="badge badge-warning">Admin</span>
<span class="badge badge-info">User</span>

Return the list of stored vCards

### `GET /accounts/{id/me}/vcards-storage/{uuid}`
<span class="badge badge-warning">Admin</span>
<span class="badge badge-info">User</span>

Return a stored vCard

### `DELETE /accounts/{id/me}/vcards-storage/{uuid}`
<span class="badge badge-warning">Admin</span>
<span class="badge badge-info">User</span>

Delete a stored vCard
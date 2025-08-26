## Contacts Lists

### `GET /contacts_lists`
<span class="badge badge-warning">Admin</span>

Show all the contacts lists.

### `GET /contacts_lists/{id}`
<span class="badge badge-warning">Admin</span>

Show a contacts list.

### `POST /contacts_lists`
<span class="badge badge-warning">Admin</span>

Create a contacts list.

JSON parameters:

* `title` **required**
* `description` **required**

### `PUT /contacts_lists/{id}`
<span class="badge badge-warning">Admin</span>

Update a contacts list.

JSON parameters:

* `title` **required**
* `description` **required**

### `DELETE /contacts_lists/{id}`
<span class="badge badge-warning">Admin</span>

Delete a contacts list.

### `POST /contacts_lists/{contacts_list_id}/contacts/{contact_id}`
<span class="badge badge-warning">Admin</span>

Add a contact to the contacts list.

### `DELETE /contacts_lists/{contacts_list_id}/contacts/{contact_id}`
<span class="badge badge-warning">Admin</span>

Remove a contact from the contacts list.

### `POST /accounts/{id}/contacts_lists/{contacts_list_id}`
<span class="badge badge-warning">Admin</span>

Add a contacts list to the account.

### `DELETE /accounts/{id}/contacts_lists/{contacts_list_id}`
<span class="badge badge-warning">Admin</span>

Remove a contacts list from the account.
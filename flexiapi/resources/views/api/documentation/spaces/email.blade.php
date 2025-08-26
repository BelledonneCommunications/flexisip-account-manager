## Spaces Email Server

### `GET /spaces/{domain}/email`
<span class="badge badge-error">Super Admin</span>

Get a space email server configuration

### `POST /spaces/{domain}/email`
<span class="badge badge-error">Super Admin</span>

Update an existing a space email server configuration.

JSON parameters:

* `host` **required**, the email server hostname
* `port` **required**, integer, the port
* `username`, the username
* `password`, the password
* `from_address`, email address, the sender email address
* `from_name`, the sender name
* `signature`, a text that will end every emails sent

### `DELETE /spaces/{domain}/email`
<span class="badge badge-error">Super Admin</span>

Delete the a space email server configuration.
## Accounts

### `POST /accounts/with-account-creation-token`
<span class="badge badge-success">Public</span>

Create an account using an `account_creation_token`.

Return `422` if the parameters are invalid or if the token is expired.

Return `403` if the `max_accounts` limit of the corresponding Space is reached.

JSON parameters:

* `username` unique username, minimum 6 characters
* `password` **required** minimum 6 characters
* `algorithm` **required**, values can be `SHA-256` or `MD5`
* `account_creation_token` the unique `account_creation_token`
* `dtmf_protocol` optional, values must be `sipinfo`, `sipmessage` or `rfc2833`

### `GET /accounts/{sip}/info`
<span class="badge badge-success">Public</span>

Retrieve public information about the account.
Return `404` if the account doesn't exists.

### `GET /accounts/me/api_key/{auth_token}`
<span class="badge badge-success">Public</span>

Generate and retrieve a fresh API Key from an `auth_token`. The `auth_token` must be attached to an existing account, see [`auth_token` attachement endpoint](#get-accountsauthtokenauthtokenattach) to do so.

Return `404` if the token is invalid or not attached.

This endpoint is also setting the API Key as a Cookie.

### `GET /accounts/me/api_key`
<span class="badge badge-info">User</span>

Generate and retrieve a fresh API Key.
This endpoint is also setting the API Key as a Cookie.

### `GET /accounts/me`
<span class="badge badge-info">User</span>

Retrieve the account information.

### `GET /accounts/me/services/turn`
<span class="badge badge-info">User</span>

If configured, returns valid TURN credentials following the [draft-uberti-behave-turn-rest-00 IEFT Draft](https://datatracker.ietf.org/doc/html/draft-uberti-behave-turn-rest-00).

### `GET /accounts/me/provision`
<span class="badge badge-info">User</span>

Provision the account by generating a fresh `provisioning_token`.

Return the account object.

### `DELETE /accounts/me`
<span class="badge badge-info">User</span>

Delete the account.

### `POST /accounts/me/password`
<span class="badge badge-info">User</span>

Change the account password.

JSON parameters:

* `algorithm` **required**, values can be `SHA-256` or `MD5`
* `old_password` **required** if the password is already set, the old password
* `password` **required**, the new password

### `POST /accounts`
<span class="badge badge-warning">Admin</span>

To create an account directly from the API.

Return `403` if the `max_accounts` limit of the corresponding Space is reached.

JSON parameters:

* `username` unique username, minimum 6 characters
* `password` **required** minimum 6 characters
* `algorithm` **required**, values can be `SHA-256` or `MD5`
* `domain` **not configurable by default**, must exist in one of the configured Spaces. Only configurable if the admin is a super admin. Otherwise the SIP domain of the corresponding space is used.
* `activated` optional, a boolean, set to `false` by default
* `display_name` optional, string
* `email` optional, must be an email, must be unique if `ACCOUNT_EMAIL_UNIQUE` is set to `true`
* `admin` optional, a boolean, set to `false` by default, create an admin account
* `phone` optional, a valid phone number, set a phone number to the account
* `dtmf_protocol` optional, values must be `sipinfo`, `sipmessage` or `rfc2833`
* `dictionary` optional, an associative array attached to the account, <a href="#dictionary">see also the related endpoints</a>.

### `PUT /accounts/{id}`
<span class="badge badge-warning">Admin</span>

Update an existing account. Ensure to resend all the parameters to not reset them.

JSON parameters:

* `username` unique username, minimum 6 characters
* `domain` **not configurable by default**, must exist in one of the configured Spaces. Only configurable if the admin is a super admin. Otherwise the SIP domain of the corresponding space is used.
* `password` **required** minimum 6 characters
* `algorithm` **required**, values can be `SHA-256` or `MD5`
* `display_name` optional, string
* `email` optional, must be an email, must be unique if `ACCOUNT_EMAIL_UNIQUE` is set to `true`
* `admin` optional, a boolean, set to `false` by default
* `phone` optional, a valid phone number, set a phone number to the account
* `dtmf_protocol` optional, values must be `sipinfo`, `sipmessage` or `rfc2833`

Using this endpoint you can also set a fresh dictionnary if the parameter is set. The existing dictionary entries will be destroyed.

* `dictionary` optional, an associative array attached to the account, <a href="#dictionary">see also the related endpoints</a>.

This endpoint also return the current `phone_change_code` and `email_change_code` if they are available.

### `GET /accounts`
<span class="badge badge-warning">Admin</span>

Retrieve all the accounts, paginated.

### `GET /accounts/{id}`
<span class="badge badge-warning">Admin</span>

Retrieve a specific account.

### `GET /accounts/{sip}/search`
<span class="badge badge-warning">Admin</span>

Search for a specific account by sip address.

### `GET /accounts/{email}/search-by-email`
<span class="badge badge-warning">Admin</span>

Search for a specific account by email.

### `DELETE /accounts/{id}`
<span class="badge badge-warning">Admin</span>

Delete a specific account and its related information.

### `POST /accounts/{id}/activate`
<span class="badge badge-warning">Admin</span>

Activate an account.

### `POST /accounts/{id}/deactivate`
<span class="badge badge-warning">Admin</span>

Deactivate an account.

### `POST /accounts/{id}/block`
<span class="badge badge-warning">Admin</span>

Block an account.

### `POST /accounts/{id}/unblock`
<span class="badge badge-warning">Admin</span>

Unblock an account.

### `GET /accounts/{id}/provision`
<span class="badge badge-warning">Admin</span>

Provision an account by generating a fresh `provisioning_token`.

### `POST /accounts/{id}/send_provisioning_email`
<span class="badge badge-warning">Admin</span>

Send a provisioning email to the account.

### `POST /accounts/{id}/send_reset_password_email`
<span class="badge badge-warning">Admin</span>

Send a password reset email to the account.

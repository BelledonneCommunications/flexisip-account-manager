## Account phone number

### `POST /accounts/me/phone/request`
<span class="badge badge-info">User</span>

Request a specific code by SMS to change the phone number.

Will return `403` if the account doesn't have a validated <a href='#account-creation-tokens'>Account Creation Token</a> attached to it.

JSON parameters:

* `phone` the phone number to send the SMS

### `POST /accounts/me/phone`
<span class="badge badge-info">User</span>

Confirm the code received and change the phone number.
Activate the account.

JSON parameters:

* `code` the received SMS code

Return the updated account.

## Accounts devices

### `GET /accounts/{id/me}/devices`
<span class="badge badge-warning">Admin</span>
<span class="badge badge-info">User</span>

Return the user registered devices.

### `DELETE /accounts/{id/me}/devices/{uuid}`
<span class="badge badge-warning">Admin</span>
<span class="badge badge-info">User</span>

Remove one of the user registered devices.

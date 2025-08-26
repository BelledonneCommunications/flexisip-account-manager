## Accounts email

### `POST /accounts/me/email/request`
<span class="badge badge-info">User</span>

Request to change the account email. An email will be sent to the new email address to confirm the operation.

Will return `403` if the account doesn't have a validated <a href='#account-creation-tokens'>Account Creation Token</a> attached to it.

JSON parameters:

* `email` the new email address, must be unique if `ACCOUNT_EMAIL_UNIQUE` is set to `true`

### `POST /accounts/me/email`
<span class="badge badge-info">User</span>

Confirm the code received and change the email.
Activate the account.

JSON parameters:

* `code` the code received by email

Return the updated account.
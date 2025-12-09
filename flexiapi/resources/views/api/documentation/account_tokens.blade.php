## Account Creation Request Tokens

An `account_creation_request_token` is a unique token that can be validated and then used to generate a valid `account_creation_token`.

### `POST /account_creation_request_tokens`
<span class="badge badge-success">Public</span>

Create and return an `account_creation_request_token` that should then be validated to be used, often using a browser CAPTCHA.

## Account Creation Tokens

An `account_creation_token` is a unique token that allow the creation or the validation of a unique account.

### `POST /account_creation_tokens/send-by-push`
<span class="badge badge-success">Public</span>

Create and send an `account_creation_token` using a push notification to the device.
Return `403` if a token was already sent, or if the tokens limit is reached for this device.
Return `503` if the token was not successfully sent.

JSON parameters:

* `pn_provider` **required**, the push notification provider, must be in apns.dev, apns or fcm
* `pn_param` the push notification parameter, can be null or contain only alphanumeric and underscore characters
* `pn_prid` the push notification unique id, can be null or contain only alphanumeric, dashes, underscore and colon characters

### `POST /account_creation_tokens/using-account-creation-request-token`
<span class="badge badge-success">Public</span>

Create an `account_creation_token` using an `account_creation_request_token`.
Return an `account_creation_token`.
Return `404` if the `account_creation_request_token` provided is not valid or expired otherwise.

JSON parameters:

* `account_creation_request_token` **required**

### `POST /account_creation_tokens/consume`
<span class="badge badge-info">User</span>

Consume an `account_creation_token` and link it to the authenticated account.
Return an `account_creation_token`.

Return `404` if the `account_creation_token` provided is not valid.

JSON parameters:

* `account_creation_token` **required**

### `POST /account_creation_tokens`
<span class="badge badge-warning">Admin</span>

Create and return an `account_creation_token`.

## Account Recovery Tokens

An `account_recovery_token` is a unique token that allow the recovery of an account.

It can be used on the following page that also accepts a `phone` optional parameter to prefil the recovery form:

    {{ route('account.recovery.show.phone', ['account_recovery_token' => '_the_token_']) }}
    {{ route('account.recovery.show.phone', ['account_recovery_token' => '_the_token_', 'phone' => '+3312341234']) }}

### `POST /account_recovery_tokens/send-by-push`
<span class="badge badge-success">Public</span>

Create and send an `account_recovery_token` using a push notification to the device.
Return `403` if a token was already sent, or if the tokens limit is reached for this device.
Return `503` if the token was not successfully sent.

JSON parameters:

* `pn_provider` **required**, the push notification provider, must be in apns.dev, apns or fcm
* `pn_param` the push notification parameter, can be null or contain only alphanumeric and underscore characters
* `pn_prid` the push notification unique id, can be null or contain only alphanumeric, dashes, underscore and colon characters

## Auth Tokens

### `POST /accounts/auth_token`
<span class="badge badge-success">Public</span>

Generate an `auth_token`. To attach the generated token to an account see [`auth_token` attachement endpoint](#get-accountsauthtokenauthtokenattach).

Return the `auth_token` object.

### `GET /accounts/auth_token/{auth_token}/attach`
<span class="badge badge-info">User</span>

Attach a publicly generated authentication token to the currently authenticated account.

Return `404` if the token is non existing or invalid.
# About

An API to deal with the Flexisip server

The API is available under `/api`

A `from` (consisting of the user SIP address, prefixed with `sip:`), `content-type` and `accept` HTTP headers are required to use the API properly

```
> GET /api/{endpoint}
> from: sip:foobar@sip.example.org
> content-type: application/json
> accept: application/json
```

# Authentication

Restricted endpoints are protected using a DIGEST authentication or an API Key mechanisms.

## Using the API Key

To authenticate using an API Key, you need to [authenticate to your account panel]({{ route('account.login') }}) and being an administrator.
On your panel you will then find a form to generate your personnal key.

You can then use your freshly generated key by adding a new `x-api-key` header to your API requests:

```
> GET /api/{endpoint}
> from: sip:foobar@sip.example.org
> x-api-key: {your-api-key}
> …
```

## Using DIGEST

To discover the available hashing algorythm you MUST send an unauthenticated request to one of the restricted endpoints.<br />
For the moment only DIGEST-MD5 and DIGEST-SHA-256 are supported through the authentication layer.

```
> GET /api/{restricted-endpoint}
> …


< HTTP 401
< content-type: application/json
< www-authenticate: Digest realm=test,qop=auth,algorithm=MD5,nonce="{nonce}",opaque="{opaque}"
< www-authenticate: Digest realm=test,qop=auth,algorithm=SHA-256,nonce="{nonce}",opaque="{opaque}"
```

You can find more documentation on the related [IETF RFC-7616](https://tools.ietf.org/html/rfc7616).

# Endpoints

## Public endpoints

### General

#### `GET /ping`
Returns `pong`

### Accounts

#### `POST /tokens`
Send a token using a push notification to the device.
Return `403` if a token was already sent, or if the tokens limit is reached for this device.
Return `503` if the token was not successfully sent.

JSON parameters:

* `pn_provider` the push notification provider
* `pn_param` the push notification parameter
* `pn_prid` the push notification unique id

#### `POST /accounts/with-token`
Create an account using a token.
Return `422` if the parameters are invalid or if the token is expired.

JSON parameters:

* `username` unique username, minimum 6 characters
* `password` required minimum 6 characters
* `algorithm` required, values can be `SHA-256` or `MD5`
* `domain` **not configurable except during test deployments** the value is enforced to the default registration domain set in the global configuration
* `token` the unique token

#### `GET /accounts/{sip}/info`
Retrieve public information about the account.
Return `404` if the account doesn't exists.

#### `POST /accounts/{sip}/activate/email`
Activate an account using a secret code received by email.
Return `404` if the account doesn't exists or if the code is incorrect, the validated account otherwise.
JSON parameters:

* `code` the code

#### `POST /accounts/{sip}/activate/phone`
Activate an account using a pin code received by phone.
Return `404` if the account doesn't exists or if the code is incorrect, the validated account otherwise.
JSON parameters:

* `code` the PIN code

## User authenticated endpoints
Those endpoints are authenticated and requires an activated account.

### Accounts

#### `GET /accounts/me`
Retrieve the account information.

#### `DELETE /accounts/me`
Delete the account.

#### `POST /accounts/me/email/request`
Change the account email. An email will be sent to the new email address to confirm the operation.
JSON parameters:

* `email` the new email address

#### `POST /accounts/me/password`
Change the account password.
JSON parameters:

* `algorithm` required, values can be `SHA-256` or `MD5`
* `old_password` required if the password is already set, the old password
* `password` required, the new password

### Accounts phone number

#### `POST /accounts/me/phone/request`
Request a specific code by SMS
JSON parameters:

* `phone` the phone number to send the SMS

#### `POST /accounts/me/phone`
Confirm the code received and change the phone number
JSON parameters:

* `code` the received SMS code

Return the updated account

### Accounts devices

#### `GET /accounts/me/devices`
Return the user registered devices.

#### `DELETE /accounts/me/devices/{uuid}`
Remove one of the user registered devices.

## Admin endpoints

Those endpoints are authenticated and requires an admin account.

### Accounts

#### `POST /accounts`
To create an account directly from the API.
If `activated` is set to `false` a random generated `confirmation_key` will be returned to allow further activation using the public endpoints. Check `confirmation_key_expires` to also set an expiration date on that `confirmation_key`.

JSON parameters:

* `username` unique username, minimum 6 characters
* `password` required minimum 6 characters
* `algorithm` required, values can be `SHA-256` or `MD5`
* `domain` **not configurable except during test deployments** the value is enforced to the default registration domain set in the global configuration
* `activated` optional, a boolean, set to `false` by default
* `admin` optional, a boolean, set to `false` by default, create an admin account
* `phone` optional, a phone number, set a phone number to the account
* `confirmation_key_expires` optional, a datetime of this format: Y-m-d H:i:s. Only used when `activated` is not used or `false`. Enforces an expiration date on the returned `confirmation_key`. After that datetime public email or phone activation endpoints will return `403`.

#### `GET /accounts`
Retrieve all the accounts, paginated.

#### `GET /accounts/{id}`
Retrieve a specific account.

#### `DELETE /accounts/{id}`
Delete a specific account and its related information.

#### `GET /accounts/{id}/activate`
Activate an account.

#### `GET /accounts/{id}/deactivate`
Deactivate an account.

### Statistics

#### `GET /statistics/day`
Retrieve registrations statistics for 24 hours.

#### `GET /statistics/week`
Retrieve registrations statistics for a week.

#### `GET /statistics/month`
Retrieve registrations statistics for a month.

# Provisioning

When an account is having an available `confirmation_key` it can be provisioned using the two following URL.

Those two URL are **not API endpoints**, they are not located under `/api`.

### `VISIT /provisioning/`
Return the provisioning information available in the liblinphone configuration file (if correctly configured).

### `VISIT /provisioning/{confirmation_key}`
Return the provisioning information available in the liblinphone configuration file.
If the `confirmation_key` is valid the related account information are added to the returned XML. The account is then considered as "provisioned" and those account related information will be removed in the upcoming requests (the content will be the same as the previous url).

### `VISIT /provisioning/qrcode/{confirmation_key}`
Return a QRCode that points to the provisioning URL.

## Authenticated provisioning

### `VISIT /provisioning/me`
Return the same base content as the previous URL and the account related information, similar to the `confirmation_key` endpoint. However this endpoint will always return those information.
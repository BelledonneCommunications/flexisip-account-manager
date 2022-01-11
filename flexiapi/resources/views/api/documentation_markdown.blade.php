# About

An API to deal with the Flexisip server

The API is available under `/api`

A `from` (consisting of the user SIP address, prefixed with `sip:`), `content-type` and `accept` HTTP headers are REQUIRED to use the API properly

```
> GET /api/{endpoint}
> from: sip:foobar@sip.example.org
> content-type: application/json
> accept: application/json
```

# Authentication

Restricted endpoints are protected using a DIGEST authentication or an API Key mechanisms.

## Using the API Key

You can retrieve an API Key from @if (config('app.web_panel')) [your account panel]({{ route('account.login') }}) @else your account panel @endif or using <a href="#get-accountsmeapikey">the dedicated API endpoint</a>.

You can then use your freshly generated key by adding a new `x-api-key` header to your API requests:

```
> GET /api/{endpoint}
> from: sip:foobar@sip.example.org
> x-api-key: {your-api-key}
> …
```

Or using a cookie:

```
> GET /api/{endpoint}
> from: sip:foobar@sip.example.org
> Cookie: x-api-key={your-api-key}
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
* `dtmf_protocol` optional, values must be `sipinfo` or `rfc2833`

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

#### `GET /accounts/me/api_key`
Generate and retrieve a fresh API Key.
This endpoint is also setting the API Key as a Cookie.

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

### Accounts contacts

#### `GET /accounts/me/contacts`
Return the user contacts.

#### `GET /accounts/me/contacts/{sip}`
Return a user contact.

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
* `domain` **not configurable by default. The value is enforced to the default domain set in the global configuration (`app.sip_domain`)**
The `domain` field is taken into account ONLY when `app.admins_manage_multi_domains` is set to `true` in the global configuration
* `activated` optional, a boolean, set to `false` by default
* `display_name` optional, string
* `admin` optional, a boolean, set to `false` by default, create an admin account
* `phone` optional, a phone number, set a phone number to the account
* `dtmf_protocol` optional, values must be `sipinfo` or `rfc2833`
* `confirmation_key_expires` optional, a datetime of this format: Y-m-d H:i:s. Only used when `activated` is not used or `false`. Enforces an expiration date on the returned `confirmation_key`. After that datetime public email or phone activation endpoints will return `403`.

#### `GET /accounts`
Retrieve all the accounts, paginated.

#### `GET /accounts/{id}`
Retrieve a specific account.

#### `GET /accounts/{sip}/search`
Search for a specific account by sip address.

#### `DELETE /accounts/{id}`
Delete a specific account and its related information.

#### `GET /accounts/{id}/activate`
Activate an account.

#### `GET /accounts/{id}/deactivate`
Deactivate an account.

### Contacts

#### `GET /accounts/{id}/contacts/`
Get all the account contacts.

#### `POST /accounts/{id}/contacts/{contact_id}`
Add a contact to the list.

#### `DELETE /accounts/{id}/contacts/{contact_id}`
Remove a contact from the list.

### Account Actions

The following endpoints will return `403 Forbidden` if the requested account doesn't have a DTMF protocol configured.

#### `GET /accounts/{id}/actions/`
Show an account related actions.

#### `GET /accounts/{id}/actions/{action_id}`
Show an account related action.

#### `POST /accounts/{id}/actions/`
Create an account action.

JSON parameters:

* `key` required, alpha numeric with dashes, lowercase
* `code` required, alpha numeric, lowercase

#### `PUT /accounts/{id}/actions/{action_id}`
Create an account action.

JSON parameters:

* `key` required, alpha numeric with dashes, lowercase
* `code` required, alpha numeric, lowercase

#### `DELETE /accounts/{id}/actions/{action_id}`
Delete an account related action.

### Account Types

#### `GET /account_types/`
Show all the account types.

#### `GET /account_types/{id}`
Show an account type.

#### `POST /account_types/`
Create an account type.

JSON parameters:

* `key` required, alpha numeric with dashes, lowercase

#### `PUT /account_types/{id}`
Update an account type.

JSON parameters:

* `key` required, alpha numeric with dashes, lowercase

#### `DELETE /account_types/{id}`
Delete an account type.

#### `POST /accounts/{id}/types/{type_id}`
Add a type to the account.

#### `DELETE /accounts/{id}/contacts/{type_id}`
Remove a a type from the account.

### Messages

#### `POST /messages`
Send a message over SIP.

JSON parameters:

* `to` required, SIP address of the receiver
* `body` required, content of the message

### Statistics

#### `GET /statistics/day`
Retrieve registrations statistics for 24 hours.

#### `GET /statistics/week`
Retrieve registrations statistics for a week.

#### `GET /statistics/month`
Retrieve registrations statistics for a month.

# Non-API Endpoints

The following URLs are **not REST API endpoints**, they are not located under `/api` but directly under the root path.

## Provisioning

When an account is having an available `confirmation_key` it can be provisioned using the two following URL.

### `GET /provisioning/`
Return the provisioning information available in the liblinphone configuration file (if correctly configured).

### `GET /provisioning/{confirmation_key}`
Return the provisioning information available in the liblinphone configuration file.
If the `confirmation_key` is valid the related account information are added to the returned XML. The account is then considered as "provisioned" and those account related information will be removed in the upcoming requests (the content will be the same as the previous url).

If the account is not activated and the `confirmation_key` is valid. The account will be activated.

### `GET /provisioning/qrcode/{confirmation_key}`
Return a QRCode that points to the provisioning URL.

## Authenticated provisioning

### `GET /provisioning/me`
Return the same base content as the previous URL and the account related information, similar to the `confirmation_key` endpoint. However this endpoint will always return those information.

## Authenticated contact list

### `GET /contacts/vcard`
Return the authenticated user contacts list, in [vCard 4.0 format](https://datatracker.ietf.org/doc/html/rfc6350).

Here is the format of the vCard list returned by the endpoint:

```
    BEGIN:VCARD
    VERSION:4.0
    KIND:individual
    IMPP:sip:schoen.tatyana@sip.linphone.org
    FN:schoen.tatyana@sip.linphone.org
    X-LINPHONE-ACCOUNT-DTMF-PROTOCOL:sipinfo
    X-LINPHONE-ACCOUNT-TYPE:phone
    X-LINPHONE-ACCOUNT-ACTION:action_key;123
    END:VCARD
    BEGIN:VCARD
    VERSION:4.0
    KIND:individual
    IMPP:sip:dhand@sip.linphone.org
    FN:dhand@sip.linphone.org
    X-LINPHONE-ACCOUNT-DTMF-PROTOCOL:sipinfo
    END:VCARD
```

### `GET /contacts/vcard/{sip}`
Return a specific user authenticated contact, in [vCard 4.0 format](https://datatracker.ietf.org/doc/html/rfc6350).
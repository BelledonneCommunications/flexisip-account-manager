# About & Auth.

An API to deal with the Flexisip server.

The API is available under `/api`

A `content-type` and `accept` HTTP headers are REQUIRED to use the API properly

```
> GET /api/{endpoint}
> from: sip:foobar@sip.example.org
> content-type: application/json
> accept: application/json
```

<div class="card bg-light mb-3">
  <div class="card-body">

Restricted endpoints are protected using a DIGEST authentication or an API Key mechanisms.

#### Access model

The endpoints are accessible using three different models:

- <span class="badge badge-success">Public</span> publicly accessible
- <span class="badge badge-info">User</span> the endpoint can only be accessed by an authenticated user
- <span class="badge badge-warning">Admin</span> the endpoint can be only be accessed by an authenticated admin user

#### Using the API Key

You can retrieve an API Key from @if (config('app.web_panel')) [your account panel]({{ route('account.login') }}) @else your account panel @endif or using <a href="#get-accountsmeapikey">the dedicated API endpoint</a>.

You can then use your freshly generated key by adding a new `x-api-key` header to your API requests:

```
> GET /api/{endpoint}
> x-api-key: {your-api-key}
> …
```

Or using a cookie:

```
> GET /api/{endpoint}
> Cookie: x-api-key={your-api-key}
> …
```

#### Using DIGEST

To discover the available hashing algorythm you MUST send an unauthenticated request to one of the restricted endpoints.<br />
Only DIGEST-MD5 and DIGEST-SHA-256 are supported through the authentication layer.

A `from` (consisting of the user SIP address, prefixed with `sip:`) header is required to initiate the DIGEST flow.

```
> GET /api/{restricted-endpoint}
> from: sip:foobar@sip.example.org
> …


< HTTP 401
< content-type: application/json
< www-authenticate: Digest realm=test,qop=auth,algorithm=MD5,nonce="{nonce}",opaque="{opaque}"
< www-authenticate: Digest realm=test,qop=auth,algorithm=SHA-256,nonce="{nonce}",opaque="{opaque}"
```

You can find more documentation on the related [IETF RFC-7616](https://tools.ietf.org/html/rfc7616).

  </div>
</div>

# Endpoints

## Ping

### `GET /ping`
<span class="badge badge-success">Public</span>
Returns `pong`

## Account Creation Request Tokens

An `account_creation_request_token` is a unique token that can be validated and then used to generate a valid `account_creation_token`.

### `POST /account_creation_request_tokens`
<span class="badge badge-success">Public</span>

Create and return an `account_creation_request_token` that should then be validated to be used.


## Account Creation Tokens

An `account_creation_token` is a unique token that allow the creation of a **unique** account.

### `POST /account_creation_tokens/send-by-push`
<span class="badge badge-success">Public</span>
Create and send an `account_creation_token` using a push notification to the device.
Return `403` if a token was already sent, or if the tokens limit is reached for this device.
Return `503` if the token was not successfully sent.

JSON parameters:

* `pn_provider` the push notification provider
* `pn_param` the push notification parameter
* `pn_prid` the push notification unique id

### `POST /account_creation_tokens/using-account-creation-request-token`
<span class="badge badge-success">Public</span>
Create an `account_creation_token` using an `account_creation_request_token`.
Return an `account_creation_token`.
Return `404` if the `account_creation_request_token` provided is not valid or expired otherwise.

JSON parameters:

* `account_creation_request_token` required

### `POST /account_creation_tokens`
<span class="badge badge-warning">Admin</span>

Create and return an `account_creation_token`.

## Auth Tokens

### `POST /accounts/auth_token`
<span class="badge badge-success">Public</span>
Generate an `auth_token`. To attach the generated token to an account see [`auth_token` attachement endpoint](#get-accountsauthtokenauthtokenattach).

Return the `auth_token` object.

### `GET /accounts/auth_token/{auth_token}/attach`
<span class="badge badge-info">User</span>
Attach a publicly generated authentication token to the currently authenticated account.

Return `404` if the token is non existing or invalid.

## Accounts

### `POST /accounts/public`

@if(config('app.dangerous_endpoints'))**Enabled on this instance**@else**Not enabled on this instance**@endif


<span class="badge badge-success">Public</span>
<span class="badge badge-warning">Unsecure endpoint</span>
Create an account.
Return `422` if the parameters are invalid.
Send an email with the activation key if `email` is set, send an SMS otherwise.

JSON parameters:

* `username` required if `phone` not set, unique username, minimum 6 characters
* `password` required minimum 6 characters
* `algorithm` required, values can be `SHA-256` or `MD5`
* `domain` if not set the value is enforced to the default registration domain set in the global configuration
* `email` optional if `phone` set, an email, set an email to the account, must be unique if `ACCOUNT_EMAIL_UNIQUE` is set to `true`
* `phone` required if `username` not set, optional if `email` set, a phone number, set a phone number to the account
* `account_creation_token` the unique `account_creation_token`

### `POST /accounts/with-account-creation-token`
<span class="badge badge-success">Public</span>
Create an account using an `account_creation_token`.
Return `422` if the parameters are invalid or if the token is expired.

JSON parameters:

* `username` unique username, minimum 6 characters
* `password` required minimum 6 characters
* `algorithm` required, values can be `SHA-256` or `MD5`
* `account_creation_token` the unique `account_creation_token`
* `dtmf_protocol` optional, values must be `sipinfo`, `sipmessage` or `rfc2833`

### `GET /accounts/{sip}/info`
<span class="badge badge-success">Public</span>
Retrieve public information about the account.
Return `404` if the account doesn't exists.

### `GET /accounts/{phone}/info-by-phone`
@if(config('app.dangerous_endpoints'))**Enabled on this instance**@else**Not enabled on this instance**@endif


<span class="badge badge-success">Public</span>
<span class="badge badge-warning">Unsecure endpoint</span>
Retrieve public information about the account.
Return `404` if the account doesn't exists.

Return `phone: true` if the returned account has a phone number.

### `POST /accounts/recover-by-phone`
@if(config('app.dangerous_endpoints'))**Enabled on this instance**@else**Not enabled on this instance**@endif


<span class="badge badge-success">Public</span>
<span class="badge badge-warning">Unsecure endpoint</span>
Send a SMS with a recovery PIN code to the `phone` number provided.
Return `404` if the account doesn't exists.

JSON parameters:

* `phone` required the phone number to send the SMS to
* `account_creation_token` the unique `account_creation_token`

### `GET /accounts/{sip}/recover/{recover_key}`
@if(config('app.dangerous_endpoints'))**Enabled on this instance**@else**Not enabled on this instance**@endif


<span class="badge badge-success">Public</span>
<span class="badge badge-warning">Unsecure endpoint</span>
Activate the account if the correct `recover_key` is provided.

The `sip` parameter can be the default SIP account or the phone based one.

Return the account information (including the hashed password) if valid.

Return `404` if the account doesn't exists.

### `POST /accounts/{sip}/activate/email`
<span class="badge badge-success">Public</span>
Activate an account using a secret code received by email.
Return `404` if the account doesn't exists or if the code is incorrect, the validated account otherwise.
JSON parameters:

* `confirmation_key` the confirmation key

### `POST /accounts/{sip}/activate/phone`
<span class="badge badge-success">Public</span>
Activate an account using a pin code received by phone.
Return `404` if the account doesn't exists or if the code is incorrect, the validated account otherwise.
JSON parameters:

* `confirmation_key` the PIN code

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

### `GET /accounts/me/provision`
<span class="badge badge-info">User</span>
Provision the account by generating a fresh `provisioning_token`.

Return the account object.

### `DELETE /accounts/me`
<span class="badge badge-info">User</span>
Delete the account.

### `POST /accounts/me/email/request`
<span class="badge badge-info">User</span>
Change the account email. An email will be sent to the new email address to confirm the operation.
JSON parameters:

* `email` the new email address, must be unique if `ACCOUNT_EMAIL_UNIQUE` is set to `true`

### `POST /accounts/me/password`
<span class="badge badge-info">User</span>
Change the account password.
JSON parameters:

* `algorithm` required, values can be `SHA-256` or `MD5`
* `old_password` required if the password is already set, the old password
* `password` required, the new password

### `POST /accounts`
<span class="badge badge-warning">Admin</span>
To create an account directly from the API.
If `activated` is set to `false` a random generated `confirmation_key` and `provisioning_token` will be returned to allow further activation using the public endpoints and provision the account. Check `confirmation_key_expires` to also set an expiration date on that `confirmation_key`.

JSON parameters:

* `username` unique username, minimum 6 characters
* `password` required minimum 6 characters
* `algorithm` required, values can be `SHA-256` or `MD5`
* `domain` **not configurable by default**. Only configurable if `APP_ADMINS_MANAGE_MULTI_DOMAINS` is set to `true` in the global configuration. Otherwise `APP_SIP_DOMAIN` is used.
* `activated` optional, a boolean, set to `false` by default
* `display_name` optional, string
* `email` optional, must be an email, must be unique if `ACCOUNT_EMAIL_UNIQUE` is set to `true`
* `admin` optional, a boolean, set to `false` by default, create an admin account
* `phone` optional, a phone number, set a phone number to the account
* `dtmf_protocol` optional, values must be `sipinfo`, `sipmessage` or `rfc2833`
* `confirmation_key_expires` optional, a datetime of this format: Y-m-d H:i:s. Only used when `activated` is not used or `false`. Enforces an expiration date on the returned `confirmation_key`. After that datetime public email or phone activation endpoints will return `403`.

### `PUT /accounts/{id}`
<span class="badge badge-warning">Admin</span>
Update an existing account.

JSON parameters:

* `username` unique username, minimum 6 characters
* `password` required minimum 6 characters
* `algorithm` required, values can be `SHA-256` or `MD5`
* `display_name` optional, string
* `email` optional, must be an email, must be unique if `ACCOUNT_EMAIL_UNIQUE` is set to `true`
* `admin` optional, a boolean, set to `false` by default, create an admin account
* `phone` optional, a phone number, set a phone number to the account
* `dtmf_protocol` optional, values must be `sipinfo`, `sipmessage` or `rfc2833`

### `GET /accounts`
<span class="badge badge-warning">Admin</span>
Retrieve all the accounts, paginated.

### `GET /accounts/{id}`
<span class="badge badge-warning">Admin</span>
Retrieve a specific account.

### `POST /accounts/{id}/recover-by-email`
<span class="badge badge-warning">Admin</span>
Send the account recovery email containing a fresh `provisioning_token` and `confirmation_key`

### `GET /accounts/{sip}/search`
<span class="badge badge-warning">Admin</span>
Search for a specific account by sip address.

### `GET /accounts/{email}/search-by-email`
<span class="badge badge-warning">Admin</span>
Search for a specific account by email.

### `DELETE /accounts/{id}`
<span class="badge badge-warning">Admin</span>
Delete a specific account and its related information.

### `GET /accounts/{id}/activate`
<span class="badge badge-warning">Admin</span>
Activate an account.

### `GET /accounts/{id}/deactivate`
<span class="badge badge-warning">Admin</span>
Deactivate an account.

### `GET /accounts/{id}/provision`
<span class="badge badge-warning">Admin</span>
Provision an account by generating a fresh `provisioning_token`.

## Accounts phone number

### `POST /accounts/me/phone/request`
<span class="badge badge-info">User</span>
Request a specific code by SMS
JSON parameters:

* `phone` the phone number to send the SMS

### `POST /accounts/me/phone`
<span class="badge badge-info">User</span>
Confirm the code received and change the phone number
JSON parameters:

* `code` the received SMS code

Return the updated account

## Accounts devices

### `GET /accounts/me/devices`
<span class="badge badge-info">User</span>
Return the user registered devices.

### `DELETE /accounts/me/devices/{uuid}`
<span class="badge badge-info">User</span>
Remove one of the user registered devices.

## Accounts contacts

### `GET /accounts/me/contacts`
<span class="badge badge-info">User</span>
Return the user contacts.

### `GET /accounts/me/contacts/{sip}`
<span class="badge badge-info">User</span>
Return a user contact.

## Contacts

### `GET /accounts/{id}/contacts`
<span class="badge badge-warning">Admin</span>
Get all the account contacts.

### `POST /accounts/{id}/contacts/{contact_id}`
<span class="badge badge-warning">Admin</span>
Add a contact to the list.

### `DELETE /accounts/{id}/contacts/{contact_id}`
<span class="badge badge-warning">Admin</span>
Remove a contact from the list.

## Account Actions

The following endpoints will return `403 Forbidden` if the requested account doesn't have a DTMF protocol configured.

### `GET /accounts/{id}/actions`
<span class="badge badge-warning">Admin</span>
Show an account related actions.

### `GET /accounts/{id}/actions/{action_id}`
<span class="badge badge-warning">Admin</span>
Show an account related action.

### `POST /accounts/{id}/actions/`
<span class="badge badge-warning">Admin</span>
Create an account action.

JSON parameters:

* `key` required, alpha numeric with dashes, lowercase
* `code` required, alpha numeric, lowercase

### `PUT /accounts/{id}/actions/{action_id}`
<span class="badge badge-warning">Admin</span>
Create an account action.

JSON parameters:

* `key` required, alpha numeric with dashes, lowercase
* `code` required, alpha numeric, lowercase

### `DELETE /accounts/{id}/actions/{action_id}`
<span class="badge badge-warning">Admin</span>
Delete an account related action.

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

* `title` required
* `description` required

### `PUT /contacts_lists/{id}`
<span class="badge badge-warning">Admin</span>
Update a contacts list.

JSON parameters:

* `title` required
* `description` required

### `DELETE /contacts_lists/{id}`
<span class="badge badge-warning">Admin</span>
Delete a contacts list.

### `POST /accounts/{id}/contacts_lists/{contacts_list_id}`
<span class="badge badge-warning">Admin</span>
Add a contacts list to the account.

### `DELETE /accounts/{id}/contacts_lists/{contacts_list_id}`
<span class="badge badge-warning">Admin</span>
Remove a contacts list from the account.

## Account Types

### `GET /account_types`
<span class="badge badge-warning">Admin</span>
Show all the account types.

### `GET /account_types/{id}`
<span class="badge badge-warning">Admin</span>
Show an account type.

### `POST /account_types`
<span class="badge badge-warning">Admin</span>
Create an account type.

JSON parameters:

* `key` required, alpha numeric with dashes, lowercase

### `PUT /account_types/{id}`
<span class="badge badge-warning">Admin</span>
Update an account type.

JSON parameters:

* `key` required, alpha numeric with dashes, lowercase

### `DELETE /account_types/{id}`
<span class="badge badge-warning">Admin</span>
Delete an account type.

### `POST /accounts/{id}/types/{type_id}`
<span class="badge badge-warning">Admin</span>
Add a type to the account.

### `DELETE /accounts/{id}/contacts/{type_id}`
<span class="badge badge-warning">Admin</span>
Remove a type from the account.

## Messages

### `POST /messages`
<span class="badge badge-warning">Admin</span>
Send a message over SIP.

JSON parameters:

* `to` required, SIP address of the receiver
* `body` required, content of the message

## Statistics

### `GET /statistics/day`
<span class="badge badge-warning">Admin</span>
Retrieve registrations statistics for 24 hours.

### `GET /statistics/week`
<span class="badge badge-warning">Admin</span>
Retrieve registrations statistics for a week.

### `GET /statistics/month`
<span class="badge badge-warning">Admin</span>
Retrieve registrations statistics for a month.

# Non-API Endpoints

The following URLs are **not API endpoints** they are not returning `JSON` content and they are not located under `/api` but directly under the root path.

## Provisioning

When an account is having an available `provisioning_token` it can be provisioned using the two following URL.

### `GET /provisioning`
<span class="badge badge-success">Public</span>
Return the provisioning information available in the liblinphone configuration file (if correctly configured).

### `GET /provisioning/{provisioning_token}?reset_password`
<span class="badge badge-success">Public</span>
Return the provisioning information available in the liblinphone configuration file.
If the `provisioning_token` is valid the related account information are added to the returned XML. The account is then considered as "provisioned" and those account related information will be removed in the upcoming requests (the content will be the same as the previous url).

If the account is not activated and the `provisioning_token` is valid. The account will be activated.

URL parameters:

* `reset_password` optional, reset the password while doing the provisioning

### `GET /provisioning/qrcode/{provisioning_token}?reset_password`
<span class="badge badge-success">Public</span>
Return a QRCode that points to the provisioning URL.

URL parameters:

* `reset_password` optional, reset the password while doing the provisioning

### `GET /provisioning/me`
<span class="badge badge-info">User</span>
Return the same base content as the previous URL and the account related information, similar to the `provisioning_token` endpoint. However this endpoint will always return those information.

## Contacts list

### `GET /contacts/vcard`
<span class="badge badge-info">User</span>
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
<span class="badge badge-info">User</span>
Return a specific user authenticated contact, in [vCard 4.0 format](https://datatracker.ietf.org/doc/html/rfc6350).
@include('api.documentation.about_auth')

# Endpoints

## Ping

### `GET /ping`
<span class="badge badge-success">Public</span>

Returns `pong`

@include('api.documentation.spaces')

@include('api.documentation.spaces.carddav')

@include('api.documentation.spaces.email')

@include('api.documentation.account_tokens')

@include('api.documentation.accounts')

@include('api.documentation.accounts.actions')

@include('api.documentation.accounts.carddav_credentials')

@include('api.documentation.accounts.contacts_lists')

@include('api.documentation.accounts.contacts')

@include('api.documentation.accounts.dictionary')

@include('api.documentation.accounts.email')

@include('api.documentation.accounts.external_account')

@include('api.documentation.accounts.phone')

@include('api.documentation.accounts.types')

@include('api.documentation.accounts.vcards_storage')

## Messages

### `POST /messages`
<span class="badge badge-warning">Admin</span>

Send a message over SIP.

JSON parameters:

* `to` **required**, SIP address of the receiver
* `body` **required**, content of the message

## Push Notifications

### `POST /push_notification`
<span class="badge badge-info">User</span>

Send a push notification using the Flexisip Pusher.

JSON parameters:

* `pn_provider` **required**, the push notification provider, must be in `apns.dev`, `apns` or `fcm`
* `pn_param` the push notification parameter, can be null or contain only alphanumeric and underscore characters
* `pn_prid` the push notification unique id, can be null or contain only alphanumeric, dashes, underscore and colon characters
* `type` **required**, must be in `background`, `message` or `call`
* `call_id` a Call ID, must have only alphanumeric, tilde and dashes characters

## Phone Countries

The phone numbers managed by FlexiAPI are validated against a list of countries that can be managed in the admin web panels.

### `GET /phones_countries`
<span class="badge badge-success">Public</span>

Return the list of Phone Countries and their current status.

If a country is deactivated all the new submitted phones submitted on the platform will be blocked.


@include('api.documentation.statistics')

JSON parameters:

* `ended_at` **required**, string, format ISO8601, when the call finished

# Non-API Endpoints

The following URLs are **not API endpoints** they are not returning `JSON` content and they are not located under `/api` but directly under the root path.

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

## vCards Storage

<!---
The following headers are mandatory to access the following endpoints:
```
> content-type: text/vcard
> accept: text/vcard
```
--->

### `GET /vcards-storage`

<span class="badge badge-info">User</span>

Return the list of stored vCards

### `GET /vcards-storage/{uuid}`
<span class="badge badge-info">User</span>

Return a stored vCard

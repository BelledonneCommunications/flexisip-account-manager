# Provisioning

Provisioning is a core concept of the FlexiAPI - Linphone clients flow.

## About

### Provisioning XML

```
<config>
  <section name="misc">
    <entry name="contacts-vcard-list" overwrite="true">https://flexiphp.bla/contacts/vcard</entry>
  </section>
  ...
</config>
```

A provisioning content example

The general idea is to allow the clients to access a unique URL returning a custom XML file containing the following elements:

* <span class="badge badge-success">Public</span> Expose the linphonerc INI file configuration
* <span class="badge badge-info">User</span> Inject the authentication information to allow the application to authenticate on the server directly if a valid account is detected using the `provisioning` token
* <span class="badge badge-success">Public</span> <span class="badge badge-info">User</span> Using __Custom Hooks__ an admin is also able to have access to the authenticated User internal object and inject custom XML during the provisioning. See the specific section in the `README.md` to learn more about that feature.

### Features

When scanning a provisioning URL with a valid token the server is also:

* <span class="badge badge-info">User</span> Activating the account
* <span class="badge badge-info">User</span> Reseting the password, generating the new authentication information and returning them (if the `reset_password` parameter is present)

## Endpoints

When an account is having an available `provisioning_token` it can be provisioned using the following URLs.

### `GET /provisioning`

<span class="badge badge-success">Public</span>

Return the provisioning information available in the liblinphone configuration file (if correctly configured).

### `GET /provisioning/auth_token/{auth_token}`

<span class="badge badge-success">Public</span>

Return the provisioning information available linked to the account that was attached to the `auth_token`.

### `GET /provisioning/{provisioning_token}?reset_password`

<span class="badge badge-success">Public</span>

Return the provisioning information available linked to the account related to the `provisioning_token`.
Return `404` if the `provisioning_token` provided is not valid or expired otherwise.

If the account is not activated the account will be activated. The account is then considered as "provisioned".

URL parameters:

* `reset_password` optional, reset the password while doing the provisioning

### `GET /provisioning/qrcode/{provisioning_token}?reset_password`

<span class="badge badge-success">Public</span>

Return a QRCode that points to the provisioning URL.

URL parameters:

* `reset_password` optional, reset the password while doing the provisioning

### `GET /provisioning/me`

<span class="badge badge-info">User</span>

Authenticated endpoint, see [API About & Auth]({{ route('api') }}#about--auth)

Return the same base content as the previous URL and the account related information, similar to the `provisioning_token` endpoint. However this endpoint will always return those information.

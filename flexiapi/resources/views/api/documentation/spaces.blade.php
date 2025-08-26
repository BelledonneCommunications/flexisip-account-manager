
## Spaces

Manage the list of allowed `spaces`. The admin accounts declared with a `domain` that is a `super` `sip_domain` will become <span class="badge badge-error">Super Admin</span>.

### `GET /spaces`
<span class="badge badge-error">Super Admin</span>

Get the list of declared Spaces.

### `GET /spaces/{domain}`
<span class="badge badge-error">Super Admin</span>

Get a Space.

### `POST /spaces`
<span class="badge badge-error">Super Admin</span>

Create a new `space`.

JSON parameters:

* `name` **required**, the space name
* `domain` **required**, the SIP domain to use, must be unique
* `host` **required**, the space host
* `account_proxy_registrar_address`, the account proxy registrar address
* `account_realm`, the default realm for the accounts, fallback to the domain if not set
* `assistant_disable_qr_code` boolean, disable the QR code feature in the assistant, default to `false`
* `assistant_hide_create_account` boolean, disable the account creation assistant, default to `false`
* `assistant_hide_third_party_account` boolean, disable the call recording feature, default to `false`
* `carddav_user_credentials` boolean, enable credentials for CardDav servers
* `copyright_text` text, the copyright text
* `custom_provisioning_entries` text, the custom configuration used for the provisioning
* `custom_provisioning_overwrite_all` boolean, allow the custom configuration to overwrite the default one
* `custom_theme` boolean, allow a custom CSS file to be loaded
* `disable_broadcast_feature` boolean, disable the broadcast feature, default to `true`
* `disable_call_recordings_feature` boolean, disable the call recording feature, default to `false`
* `disable_chat_feature` boolean, disable the chat feature, default to `false`
* `disable_meetings_feature` boolean, disable the meeting feature, default to `false`
* `expire_at` date, the moment the space is expiring, default to `null` (never expire)
* `hide_account_settings` boolean, disable the account settings, default to `false`
* `hide_settings` boolean, hide the app settings, default to `false`
* `intercom_features` boolean, the intercom features switch
* `intro_registration_text` Markdown text, the main registration page text
* `max_account` integer, the maximum number of accounts configurable in the app, default to `0` (infinite)
* `max_accounts` integer, the maximum number of accounts that can be created in the space, default to `0` (infinite), cannot be less than the actual amount of accounts
* `newsletter_registration_address`, the newsletter registration email address
* `only_display_sip_uri_username` boolean, hide the SIP uris in the app, default to `false`
* `phone_registration` boolean, the phone registration switch
* `provisioning_use_linphone_provisioning_header` boolean
* `public_registration` boolean, the public registration switch
* `super` boolean, set the domain as a Super Domain
* `web_panel` boolean, the web panel switch

### `PUT /spaces/{domain}`
<span class="badge badge-error">Super Admin</span>

Update an existing `sip_domain`.

JSON parameters:

* `account_proxy_registrar_address`, **required**, the account proxy registrar address
* `account_realm`, **required**, the default realm for the accounts, fallback to the domain if not set
* `assistant_disable_qr_code` **required**, boolean
* `assistant_hide_create_account` **required**, boolean
* `assistant_hide_third_party_account` **required**, boolean
* `carddav_user_credentials` **required** boolean, enable credentials for CardDav servers
* `copyright_text` **required**, text, the copyright text
* `custom_provisioning_entries` **required**, text, the custom configuration used for the provisioning
* `custom_provisioning_overwrite_all` **required**, boolean, allow the custom configuration to overwrite the default one
* `custom_theme` **required**, boolean, allow a custom CSS file to be loaded
* `disable_broadcast_feature` **required**, boolean
* `disable_call_recordings_feature` **required**, boolean
* `disable_chat_feature` **required**, boolean
* `disable_meetings_feature` **required**, boolean
* `expire_at` **required**, date, the moment the space is expiring, set to `null` to never expire
* `hide_account_settings` **required**, boolean
* `hide_settings` **required**, boolean
* `intercom_features` **required**, boolean, the intercom features switch
* `intro_registration_text` **required**, Markdown text, the main registration page text
* `max_account` **required**, integer
* `max_accounts` **required**,integer, the maximum number of accounts that can be created in the space, default to `0` (infinite), cannot be less than the actual amount of accounts
* `name` **required**, the space name
* `newsletter_registration_address`, **required**, the newsletter registration email address
* `only_display_sip_uri_username` **required**, boolean
* `phone_registration` **required**, boolean, the phone registration switch
* `provisioning_use_linphone_provisioning_header` **required**, boolean
* `public_registration` **required**, boolean, the public registration switch
* `super` **required**, boolean, set the domain as a Super Domain
* `web_panel` **required**, boolean, the web panel switch

### `DELETE /spaces/{domain}`
<span class="badge badge-error">Super Admin</span>

Delete a domain, **be careful, all the related accounts will also be destroyed**.

# Releases

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/).

## [2.0]

### Added

- **Spaces:** A new way to manage your SIP domains and hosts. A Space is defined by a unique SIP Domain and Host pair.
    - **New mandatory DotEnv variable** `APP_ROOT_HOST`, replaces `APP_URL` and `APP_SIP_DOMAIN` that are now configured using the new dedicated Artisan script. It defines the root hostname where all the Spaces will be configured. All the Spaces will be as subdomains of `APP_ROOT_HOST` except one that can be equal to `APP_ROOT_HOST`. Example: if `APP_ROOT_HOST=myhost.com` the Spaces hosts will be `myhost.com`, `alpha.myhost.com` , `beta.myhost.com`...
    - **New DotEnv variable:** `APP_ACCOUNT_RECOVERY_TOKEN_EXPIRATION_MINUTES=0` Number of minutes before expiring the recovery tokens
    - **New Artisan script** `php artisan spaces:create-update {sip_domain} {host} {name} {--super}`, replaces `php artisan sip_domains:create-update {sip_domain} {--super}`. Can create a Space or update a Space Host base on its Space SIP Domain.
- **Push Notification endpoint** Add a /push-notification endpoint to send custom push notifications to the Flexisip Pusher
- **Add internationalisation support in the app** The web panels are now available in French and English
- **Add External Accounts** In the API and web panels, allowing users to setup an external account that can be used in another service like the B2BUA
- **Add configurable admin API Keys** Allowing admins to setup non-expiring services API Keys
- **Add provisioning email** A user can now receive a custom generated email with all the provisioning related information
- **Add API endpoints to send the password reset and provisioning emails**
- **Add an app setup wizard page** Static web page inviting the users to download the app if it is not installed yet

### Changed

- **Removing and moving DotEnv instance environnement variables to the Spaces** The following DotEnv variables were removed. You can now configure them directly in the designated spaces after the migration.
    - INSTANCE_COPYRIGHT
    - INSTANCE_INTRO_REGISTRATION
    - INSTANCE_CUSTOM_THEME
    - WEB_PANEL
    - PUBLIC_REGISTRATION
    - PHONE_AUTHENTICATION
    - DEVICES_MANAGEMENT
    - INTERCOM_FEATURES
    - NEWSLETTER_REGISTRATION_ADDRESS
    - ACCOUNT_PROXY_REGISTRAR_ADDRESS
    - ACCOUNT_TRANSPORT_PROTOCOL_TEXT
    - ACCOUNT_REALM
    - ACCOUNT_PROVISIONING_RC_FILE
    - ACCOUNT_PROVISIONING_OVERWRITE_ALL
    - ACCOUNT_PROVISIONING_USE_X_LINPHONE_PROVISIONING_HEADER
- **Complete and reorganize the Markdown documentation**
- **Refactor the emails templates** All the emails were modernized and are now generated in HTML

### Removed

- **Remove the deprecated endpoints** The endpoints inherited from XMLRPC are now completely removed

### Migrate from [1.6]

1. Deploy the new version and migrate the database.

```
php artisan migrate
```

2. Set `APP_ROOT_HOST` in `.env` or as an environnement variable. And remove `APP_URL` and `APP_SIP_DOMAIN`

```
APP_ROOT_HOST=myhost.com
```

3. The migration script will automatically copy the `sip_domain` into `host` in the `spaces` table. You then have to "fix" the hosts and set them to equal or be subdomains of `APP_ROOT_HOST`.

```
php artisan spaces:create-update my.sip myhost.com "My Super Space" --super # You can set some Spaces as SuperSpaces, the admin will be able to manage the other spaces
php artisan spaces:create-update alpha.sip alpha.myhost.com "Alpha Space"
php artisan spaces:create-update beta.sip beta.myhost.com "Beta Space"
...
```

4. Configure your web server to point the `APP_ROOT_HOST` and subdomains to the app. See the related documentation in [`INSTALL.md` file](INSTALL.md#31-mandatory-app_root_host-variable).

5. Configure your Spaces.

6. (Optional) Import the old instance DotEnv environnement variables into a space.

7. Remove the instance based environnement variables (see **Changed** above) and configure them directly in the spaces using the API or Web Panel.

⚠️ Be careful, during this import only the project DotEnv file variables will be imported, other environnement (eg. set in Apache, nginx or Docker) will be ignored.

⚠️ The content of the `ACCOUNT_PROVISIONING_RC_FILE` will not be imported. You will have to extract the sections and lines that you want to use manually using the dedicated form or the API.

```
php artisan spaces:import-configuration-from-dot-env {sip_domain}
```

You can find more details regarding those steps in the [`INSTALL.md`](INSTALL.md) and [`README.md`](README.md) files.


## [1.6] - 2024-12-30

### Added

- **Allow the expiration of tokens and codes in the DotEnv configuration**
    - **New DotEnv variables:** check all the new `*_EXPIRATION_MINUTES` for each token and code in `.env.example`
- **Phone validation system by country code:** all the provided phone numbers are now properly validated and some countries can be forbidden
- **SIP Domain management:** the account domains are now managed in a set of panels and API endpoints, this is the base of the upcoming space administration system
- **JSON validation in the API:** the provised JSON is now validated and returns an error if an issue is detected
- **CoTURN credentials support:** TURN credentials can now be generated and return through the provisioning feature
- **RFC 8898 Support**

## Changed

- **Replace Material Icons with Phosphor**

## Deprecated

- **Last major version supporting the deprecated endpoints of the API**

### Migrate from [1.5]

Nothing specific to do

## [1.5] - 2024-08-29

### Added

- **Account activity view:** new panel, available behind the Activity tab, will allow any admin to follow the activity of the accounts they manage.
- **Detect and block abusive accounts:** This activity tracking is coming with a related tool that is measuring the accounts activity and automatically block them if it detects some unusual behaviors on the service. An account can also directly be blocked and unblocked from the setting panel. Two new setting variables will allow you to fine tune those behaviors triggers.
    - **New DotEnv variable:** `BLOCKING_TIME_PERIOD_CHECK=30` Time span on which the blocking service will proceed, in minutes
    - **New DotEnv variable:** `BLOCKING_AMOUNT_EVENTS_AUTHORIZED_DURING_PERIOD=5` Amount of account events authorized during this period
- **OAuth JWT Authentication:** OAuth support with the handling of JWE tokens issues by a third party service such as Keycloack.
    - **New DotEnv variable:** `JWT_RSA_PUBLIC_KEY_PEM=`
    - **New DotEnv variable:** `JWT_SIP_IDENTIFIER=sip_identifier`
- **Super-domains and super-admins support:** Introduce SIP domains management. The app accounts are now divided by their domains with their own respective administrators that can only see and manage their own domain accounts and settings. On top of that it is possible to configure a SIP domain as a "super-domain" and then allow its admins to become "super-admins". Those super-admins will then be able to manage all the accounts handled by the instance and create/edit/delete the other SIP domains. Add new endpoints and a new super-admin role in the API to manage the SIP domains. SIP domains can also be created and updated directly from the console using a new artisan script (documented in the README);
    - **New Artisan script:** `php artisan sip_domains:create-update {domain} {--super}`
- **Account Dictionary:** Each account can now handle a specific dictionary, configurable by the API or directly the web panel. This dictionary allows developers to store arbitrary `key -> value pairs` on each accounts.
- **Vcard storage:** Attach custom vCards on a dedicated account using new endpoints in the API. The published vCard are validated before being stored.

### Changed

- **User management of their own devices:** Allowing users will be able to manage its own devices. Specific API endpoints were also added to manage them directly from the clients.
- **Migration to hCaptcha:** Migrate from Google Recaptcha to hCaptcha in this release.
    - **New DotEnv variable:** HCAPTCHA_SECRET=secret-key
    - **New DotEnv variable:** HCAPTCHA_SITEKEY=site-key
- **Localization support:** The API is now accepting the `Accept-Language` header and adapt its internal localization to the client/browser one. For the moment only French and English are supported but more languages could be added in the future.

# Releases


All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/).

## [1.5] - 2024-08-29

### Added

- **Account activity view:** new panel, available behind the Activity tab, will allow any admin to follow the activity of the accounts they manage.
- **Detect and block abusive accounts:** This activity tracking is coming with a related tool that is measuring the accounts activity and automatically block them if it detects some unusual behaviors on the service. An account can also directly be blocked and unblocked from the setting panel. Two new setting variables will allow you to fine tune those behaviors triggers.
    - **New DotEnv variable:** `BLOCKING_TIME_PERIOD_CHECK=30` # Time span on which the blocking service will proceed, in minutes
    - **New DotEnv variable:** `BLOCKING_AMOUNT_EVENTS_AUTHORIZED_DURING_PERIOD=5` # Amount of account events authorized during this period
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

### Deprecated

- **Last major version supporting the deprecated endpoints of the API**


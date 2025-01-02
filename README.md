# Introduction

Flexisip Account Manager brings several tools in one:

- FlexiAPI, a REST API for the creation and management of user accounts by end-users and/or service administrators.
- a web portal, powered by FlexiAPI
- a remote provisioning server, able to generate configuration files compatible with Linphone's QrCode-based or URL-based remote provisioning feature

# License

Copyright © Belledonne Communications

Flexisip is dual licensed, and can be licensed and distributed:

- under a GNU Affero GPLv3 license for free (see COPYING file for details)
- under a proprietary license, for closed source projects. Contact Belledonne Communications for any question about costs and services.

# Documentation

Once deployed you can have access to the global and API documentation on the `/api` and `/documentation` pages.

# Setup

Check the `INSTALL.md` file.

## Usage

For the web panel, a general documentation is available under the `/documentation` page.
For the REST API, the `/api` page contains all the required documentation to authenticate and request the API.
FlexiAPI is also providing endpoints to provision Liblinphone powered devices. You can find more documentation about it on the `/provisioning/documentation` documentation page.

## Console commands

FlexiAPI is shipped with several console commands that you can launch using the `artisan` executable available at the root of this project.

### Create or update a Space

Create or update a Space, required to then create accounts afterward. The `super` option enable/disable the domain as a super domain.

    php artisan spaces:create-update {sip_domain} {host} {--super}

### Create an admin account

Create an admin account, an API Key will also be generated along the way, it might expire after a while.

If no parameters are put, a default admin account will be created.

    php artisan accounts:create-admin-account {-u|username=} {-p|password=} {-d|domain=}

### Clear the expired API Keys

This will remove the API Keys that were not used after `x minutes`.

    php artisan digest:clear-api-keys {minutes}

### Clear Expired Nonces for DIGEST authentication

This will remove the nonces stored that were not updated after `x minutes`.

    php artisan digest:clear-nonces {minutes}

### Remove the unconfirmed accounts

This request will remove the accounts that were not confirmed after `x days`. In the database an unconfirmed account is having the `activated` attribute set to `false`.

    php artisan accounts:clear-unconfirmed {days} {--apply} {--and-confirmed}

The base request will not delete the related accounts by default. You need to add `--apply` to remove them.

### Remove deleted accounts tombstones

This request will remove the deleted accounts tombstones created after `x days`.

    php artisan accounts:clear-accounts-tombstones {days} {--apply}

The base request will not delete the related tombstones by default. You need to add `--apply` to remove them.

### Set an account admin

This command will set the admin role to any available Flexisip account. You need to use the account DB id as a parameter in this command.

    php artisan accounts:set-admin {account_id}

Once one account is declared as administrator, you can directly configure the other ones using the web panel.

### Seed liblinphone test accounts

You can also seed the tables with test accounts for the liblinphone test suite with the following command (check LiblinphoneTesterAccoutSeeder for the JSON syntax):

    php artisan accounts:seed /path/to/accounts.json

## SMS templates

To send SMS to the USA some providers need to validate their templates before transfering them, see [Sending SMS messages to the USA - OVH](https://help.ovhcloud.com/csm/en-ie-sms-sending-sms-to-usa?id=kb_article_view&sysparm_article=KB0051359).

Here are the currently used SMS templates in the app to declare in your provider panel:

- Creation code: `Your #APP_NAME# creation code is #CODE#`. Sent to confirm the creation of the account by SMS.
- Recovery code: `Your #APP_NAME# recovery code is #CODE#`. Sent to recover the account by SMS.
- Validation code: `Your #APP_NAME# validation code is #CODE#`. Sent to validate the phone change by SMS.
- Validation code with expiration: `Your #APP_NAME# validation code is #CODE#. The code is available for #CODE_MINUTES# minutes`. Sent to validate the phone change by SMS, include an expiration time.

## Custom email templaces

Some email templates can be customized.

To do so, copy and rename the existing `*_custom.blade.php.example` files into `*custom.blade.php` and adapt the content of the email (HTML and text versions), those files will then replace the default ones.

## Hooks

### Provisioning hooks

The XML returned by the provisioning endpoint can be completed using hooks.

To do so, copy and rename the `provisioning_hooks.php.example` file into `provisioning_hooks.php` in the configuration directory and complete the functions in the file.
The functions already contains example codes to show you how the XML can be enhanced or completed.

### Account Service hooks

The internal Account Service is also providing hooks. Rename and complete the following file to enable and use them: `account_service_hooks.php.example`.

## Sending SIP messages from the API

The `POST /api/messages` endpoint allows you to send messages on the SIP network. It call internally `linphone-daemon` to do so. To be able to use it you should follow the following steps:

1. Launch the `linphone-daemon` with a UNIX socket path, this will create a socket file in `/tmp` (the file will be `/tmp/lp` for the following line).

    $ linphone-daemon --pipe ld

2. Configure the `.env` file to point to that UNIX socket

    APP_LINPHONE_DAEMON_UNIX_PATH=/tmp/ld

If you have issues connecting to that socket check the [`systemd restrictions`](#systemd-restrictions) part of this document.

The socket is located in the `/tmp` directory.

The systemd service [PrivateTmp](https://access.redhat.com/blogs/766093/posts/1976243) setting might also restrict that access.

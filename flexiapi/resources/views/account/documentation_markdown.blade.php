# Introduction

[{{ $app_name }}]({{ route('account.home') }}) is a tool that is completing some features around the Flexisip server.

The application is connected to the Flexisip server and directly interact with it to offer a web interface providing those features.

# Registration

Registration can be achieve using several methods if they are correctly configured and enabled by your administrator.

## Email Registration

@if (!config('app.web_panel') || !config('app.public_registration'))
*The feature is not enabled on this instance.*
@endif

You can @if (config('app.web_panel') && config('app.public_registration')) [create an account using an email address]({{ route('account.register.email') }}) @else create an account using an email address @endif. The form requires you to provide an username and your email address.

Once completed a confirmation email containing a unique link will be sent to the address. This link is used to activate your account, allowing you to finish the setup.

## Account Creation Token Registration

Allow the creation of an account using a previously generated Account Creation Token.

## Phone Registration

@if (!config('app.phone_authentication'))
*The feature is not enabled on this instance.*
@endif

If enabled you can also @if (config('app.web_panel') && config('app.phone_authentication')) [create an account using a phone number]({{ route('account.register.phone') }}) @else create an account using a phone number @endif. You can also add an optional nickname to personnalize your SIP address. If not, your phone number will be used as a username.

Once submitted, you will be asked to provide a unique pin code received by SMS to the phone number used during the registration.

## Password completion

Once activated {{ $app_name }} will ask your to provide a password to finish your account setup.

# Authentication

To authenticate please fill in the username or phone number and password you provided during the registration phase.

If you forgot your password or didn't configured it, you can always recover your account using the recover password forms, using your @if (config('app.web_panel')) [email address]({{ route('account.recovery.show.email') }}) @else email address @endif or @if (config('app.web_panel') && config('app.phone_authentication')) [phone number]({{ route('account.recovery.show.phone') }}) @else phone number (not enabled) @endif. Once authenticated you will then be able to change your password.

## Code based authentication

[{{ $app_name }}]({{ route('account.home') }}) allows you to authenticate a new device using an already authenticated one. This can be done by generating a QRCode from the authenticated device and flash it on the un-authenticated one (or the other way around).

# Account panel

Once authenticated you will get access to @if (config('app.web_panel')) [your account panel]({{ route('account.dashboard') }}) @else your account panel @endif.

## Generate an API Key

You will be able to generate an API Key allowing you to use the {{ $app_name }} API with the API Key authentication mechanism. Check the related [API Documentation]({{ route('api') }}) to know more about this feature.

## Change your email address

You can @if (config('app.web_panel')) [change your email address]({{ route('account.email.change') }}) @else change your email address @endif from the panel. A confirmation email containing a unique link will be sent to validate the new one.

## Change your password

Your password can also be changed from the @if (config('app.web_panel')) [password change form]({{ route('account.password.show') }}) @else password change form @endif. You can enable SHA-256 encrypted password when changing it (required for some clients).

## Delete your account

Your account can be deleted from the panel using the @if (config('app.web_panel')) [account deletion form]({{ route('account.delete') }}) @else account deletion form @endif. You must re-enter your full SIP address to confirm the deletion.

## Devices management

@if (config('app.devices_management') == false)
*The feature is not enabled on this instance.*
@endif

From the devices management panel an admin will be able to list and delete the devices attached to a SIP account.

# Admin panel

This panel is only accessible to admin accounts.

## Accounts administration

From the accounts administration panel an administrator will be able to list, create, show, edit and and delete accounts from the attached Flexisip server.

### Display a user account

Each user, identified by a unique number can be managed from the panel. The account can be activated or deactivated (see the Registration section for more information about activation).

You can also set an account as an administrator. The account will then have the same accesses and authorizations as you.

Finally the account page allows you to provision the account, using a QR Code or a unique link that can be shared with the contact.

The provisioning link can be generated and refreshed from this page as well. If the provisiong link is renewed, the old one will be unavailable.

### Create and edit an account

Administrators can create and edit accounts directly from the admin panel. During the edition they can assign contacts (other accounts available in the local database), actions and types the same way it can be done in the [API]({{ route('api') }}).

### Delete an account

The deletion of an account is definitive, all the database related data (password, aliases…) will be destroyed after the deletion.

### Create, edit and delete account types

@if (config('app.intercom_features') == false)
*The feature is not enabled on this instance.*
@endif

An adminisator can create, edit and delete account types. Those can be used to categorize accounts in clients, they are often used for Internet of Things related devices.

## Statistics

The statistics panel show different statistics recorder by the Account Manager, they can be explored, filtered and exported.
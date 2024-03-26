# Flexisip Account Manager Changelog

v1.5
----
- Fix FLEXIAPI-153 Add phone and email to be changed in the Activity panel
- Fix FLEXIAPI-151 Migrate to hCaptcha
- Fix FLEXIAPI-150 Use the same account_id parameter for both API and Web routes
- Fix FLEXIAPI-149 Add a toggle to disable phone check on username for admin endpoints and forms
- Fix FLEXIAPI-148 Reuse AccountService in the POST /api/accounts admin endpoint
- FIX FLEXIAPI-146 Allow users to manage their own devices
- Fix FLEXIAPI-145 Put back the 'code' parameter as an alias for the 'confirmation_key' for the activateEmail and activatePhone endpoints
- Fix FLEXIAPI-144 Introduce APP_FLEXISIP_PUSHER_FIREBASE_KEYSMAP as a replacement for APP_FLEXISIP_PUSHER_FIREBASE_KEY
- Fix FLEXIAPI-143 JWT Authentication layer on the API
- Fix FLEXIAPI-142 PUT /accounts endpoint doesn't allow overiding values anymore
- Fix FLEXIAPI-140 Fix the display_name attribute in the Vcard4 render
- Fix FLEXIAPI-139 Refactor the email and phone API documentation
- Fix FLEXIAPI-138 Add ip and user_agent columns to all the tokens and code tables, fill the values when required and display them in the admin
- Fix FLEXIAPI-136 Refactor the Web Panel toggle mechanism and move it to a proper Middleware
- Fix FLEXIAPI-135 Merge the admins table in the accounts table
- Fix FLEXIAPI-134 Add a system to detect and block abusive accounts
- Fix FLEXIAPI-133 Use the correct breadcrumb on create and fix a password
- Fix FLEXIAPI-132 Refactor the Provisioning to remove proxy_default_values
- Fix #143 Ensure that the ProvisioningToken model behave likes all the other Consommable
- Fix #141 Add a new hook system for the Account Service
- Fix #138 Add a dictionary attached to the accounts
- Fix #137 Migrate the icons from Material Icons to Material Symbols
- Fix #135 Refactor the password algorithms code
- Fix #134 Create an Activity view in the Admin > Accounts panel
- Fix #133 Make the MySQL connection unstrict
- Fix #132 Move the provisioning_tokens and recovery_codes to dedicated table
- Fix #130 Drop the group column in the Accounts table

v1.4.4
------
- Fix FLEXIAPI-136 Refactor the Web Panel toggle mechanism and move it to a proper Middleware

v1.4.3
------
- Fix FLEXIAPI-133 Use the correct breadcrumb on create and fix a password update related issue on update

v1.4.2
------
- Fix #135 Refactor the password algorithms code

v1.4.1
------
- Fix #133 Make the MySQL connection unstrict

v1.4
----
- Redesign and refactoring of the main UI and panel flows
- Complete the statistics and add a specific API to get usage statistics from FlexiAPI
- Removal of XMLRPC
- Add RockyLinux 9 support
- Add Debian 12 to CI
- Fix #122 Add a new console command CreateFirstAdmin
- Fix #121 Only apply throttling to redeemed tokens
- Fix #123 Define a proper documentation for the provisioning flow
- Fix #124 Return 404 when the account is already provisioned or the provisioning_token not valid
- Fix #125 Remove the External Accounts feature
- Fix #19 Set all the ERROR confirmation_key to null in the accounts table

v1.3
----
- Fix #90 Deploy packages from release branches as well
- Fix #58 Fix the packaging process to use git describe as a reference
- Fix #58 Move the generated packages in the build directory, and fix the release and version format in the .spec
- Fix #58 Refactor and cleanup the .gitlab-ci file
- Move the minimum PHP version to 8.0
- Fix #47 Move the docker to an external repository
- Fix #83 Add php-redis-remi package
- Fix #85 Also package php-pecl-igbinary and php-pecl-msgpack from remi
- Fix #84 Remove CentOS7 from the pipeline
- Fix #80 Inject provisioning link and QRCode in the default email with a password_reset parameter
- Fix #79 Add a refresh_password parameter to the provisioning URLs
- Fix #78 Add a APP_ACCOUNTS_EMAIL_UNIQUE environnement setting
- Fix #30 Remove APP_EVERYONE_IS_ADMIN

v1.2
----

- Introduce FlexiAPI built on Laravel to replace XMLRPC
- Deprecates XMLRPC (will be removed in the 2.0 release)
- Create a REST API to manage the accounts, related features and provisioning
- Create a user web panel for their account management, currently in testing phase (unstable)
- Create an admin web panel to manage accounts and related features
- Allow accounts to be exported as ExternalAccounts and imported in another Flexisip Account Manager instance
- Add various artisan console commands to maintain the data (cleaning up, importing, exporting, seeding)
- Add unit tests for the FlexiAIP REST API
- Rebuild the existing database using the Laravel migration scripts

# Flexisip Account Manager Changelog

v1.5
---
- Fix FLEXIAPI-195 Fix LiblinphoneTesterAccoutSeeder to fit with the latest Account related changes
- Fix FLEXIAPI-193 Typo
- Fix FLEXIAPI-192 Clear and upgrade properly the account dictionary entries if the entries are already existing
- Fix FLEXIAPI-191 Add quotes for the pn-prid parameter in FlexisipPusherConnector
- Fix FLEXIAPI-186 Ensure that empty objects are serialized in JSON as objects and not empty arrays
- Fix FLEXIAPI-185 Return null if the account dictionary is empty in the API
- Fix FLEXIAPI-184 Append phone_change_code and email_change_code to the admin /accounts/<id> endpoint if they are available
- Fix FLEXIAPI-183 Complete the account hooks on the dictionnary actions
- Fix FLEXIAPI-182 Replace APP_SUPER_ADMINS_SIP_DOMAINS with a proper sip_domains table, API endpoints, UI panels, console command, tests and documentation
- Fix FLEXIAPI-181 Replace APP_ADMINS_MANAGE_MULTI_DOMAINS with APP_SUPER_ADMINS_SIP_DOMAINS
- Fix FLEXIAPI-180 Fix the token and activation flow for the provisioning with token endpoint when the header is missing
- Fix FLEXIAPI-179 Add Localization support as a Middleware that handles Accept-Language HTTP header
- Fix FLEXIAPI-178 Show the unused code in the Activity tab of the accounts in the admin panel
- Fix FLEXIAPI-177 Complete vcards-storage and devices related endpoints with their User/Admin ones
- Fix FLEXIAPI-176 Improve logs for the deprecated endpoints and AccountCreationToken related serialization
- Fix FLEXIAPI-175 and FLEXISIP-231 Rewrite the Redis contacts parser to handle properly SIP uris (thanks @thibault.lemaire !)
- Fix FLEXIAPI-174 Check if the phone is valid before trying to recover it (deprecated endpoint)
- Fix FLEXIAPI-173 Wrong route in validateEmail (deprecated)
- Fix FLEXIAPI-171 Fix README documentation for CreateAdminAccount
- Fix FLEXIAPI-170 Fix undefined variable apiKey in CreateAdminAccount
- Fix FLEXIAPI-168 Add POST /accounts/me/email to confirm the email change
- Fix FLEXIAPI-167 Add the handling of a custom identifier for the JWT tokens on top of the email one
- Fix FLEXIAPI-166 Reimplement the deprecated email validation URL
- Fix FLEXIAPI-165 Remove for now text/vcard header constraint
- Fix FLEXIAPI-164 Add vcards-storage endpoints
- Fix FLEXIAPI-163 Complete AccountService hooks
- Fix FLEXIAPI-162 Drop the aliases table and migrate the data to the phone column
- Fix FLEXIAPI-161 Complete the Dictionary tests to cover the collection accessor
- Fix FLEXIAPI-159 Add the account_creation_tokens/consume endpoint
- Fix FLEXIAPI-158 Restrict the phone number change API endpoint to return 403 if the account doesn't have a validated Account Creation Token
- Fix FLEXIAPI-156 Disable the Phone change web form when PHONE_AUTHENTICATION is disabled
- Fix FLEXIAPI-155 Add a new accountServiceAccountUpdatedHook and accountServiceAccountDeletedHook
- Fix FLEXIAPI-153 Add phone and email to be changed in the Activity panel
- Fix FLEXIAPI-152 API Key usage clarification
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

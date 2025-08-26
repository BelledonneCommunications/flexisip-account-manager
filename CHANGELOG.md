# Flexisip Account Manager Changelog

v2.1
----
- Fix FLEXIAPI-282 Migrate to Laravel 11 and PHP 8.2+
- Fix FLEXIAPI-371 Add documentation for the Wizard page
- Fix FLEXIAPI-359 Add CardDav servers support in the spaces

v2.0
----
- Fix FLEXIAPI-205 Remove the deprecated endpoints, compatibility code documentation and tests. Drop the confirmation_key accounts column and activation_expirations table
- Fix FLEXIAPI-206 Upgrade to Laravel 10, PHP 8.1 minimum and bump all the related dependencies, drop Debian 11 Bullseye
- Fix FLEXIAPI-220 Migrate SIP Domains to Spaces
- Fix GH-15 Add password import from CSV
- Fix FLEXIAPI-242 Add stricter validation for the AccountCreationToken Push Notification endpoint
- Fix FLEXIAPI-241 Add a /push-notification endpoint to send custom push notifications to the Flexisip Pusher
- Fix FLEXIAPI-244 Remove faulty middleware
- Fix FLEXIAPI-250 Allow Spaces to be declared without a subdomain
- Fix FLEXIAPI-252 Update the hCaptcha Laravel library, use file instead of cookies to store the session to prevent empty errors bags
- Fix FLEXIAPI-254 Allow no data on POST requests to not trigger the ValidateJSON middleware
- Fix FLEXIAPI-255 Create a INSTALL.md tutorial and log FlexisipPusherConnector errors
- Fix FLEXIAPI-257 Return a more coherent message when search API endpoints returns a 404
- Fix FLEXIAPI-260 Return 404 and not 403 if the contact is already in the list or missing when removing it
- Fix FLEXIAPI-262 Bypass the JWT auth if we have an API Key
- Fix FLEXIAPI-264 Add -k|api_key_ip parameter to accounts:create-admin-account to set/clear the related API Key restriction
- Fix FLEXIAPI-256 Publish an empty string while deleting a device on Redis to force the refresh on the other clients
- Fix FLEXIAPI-268 Allow pn-param in Apple format for the push notifications endpoints
- Fix FLEXIAPI-269 Update the IsNotPhoneNumber rule to use a better phone number validator
- Fix FLEXIAPI-258 Move DotEnv instance configurations in the Spaces table
- Fix FLEXIAPI-270 Call the static $apnsTypes attribute in FlexisipPusherConnector
- Fix FLEXIAPI-271 Handle properly reversed attributes in objects
- Fix FLEXIAPI-237 Add internationalisation support in the app
- Fix FLEXIAPI-261 Remove the TURN part in the XML provisioning (and only keep the API endpoint)
- Fix FLEXIAPI-275 Add names in Spaces
- Fix FLEXIAPI-278 Complete and reorganize the Markdown documentation
- Fix FLEXIAPI-233 Add External Accounts (new version)
- Fix FLEXIAPI-277 Restrict authorized ini keys that can be set to prevent conflict with the existing ones set in the UI
- Fix FLEXIAPI-272 Add Space based email server integration
- Fix FLEXIAPI-284 Add configurable admin API Keys
- Fix FLEXIAPI-232 Add provisioning email + important redesign of the contacts page
- Fix FLEXIAPI-287 Refactor the emails templates
- Fix FLEXIAPI-286 Send an account_recovery_token using a push notification and protect the account recovery using phone page with the account_recovery_token
- Fix FLEXIAPI-293 Remove the (long) outdated general documentation
- Fix FLEXIAPI-224 Add a console script to send Space Expiration emails
- Fix FLEXIAPI-297 Fix PrId and CallId validations
- Fix FLEXIAPI-305 Add specific error page for Space Expiration
- Fix FLEXIAPI-169 Added missing selinux label to log files and storage directory
- Fix FLEXIAPI-313 Fix the admin device deletion link, recover the missing...
- Fix FLEXIAPI-318 Fix email recovery validation
- Fix FLEXIAPI-319 Fix the admin device deletion link, recover the missing method
- Fix FLEXIAPI-321 Disable the account creation button when the Space is full for admins
- Fix FLEXIAPI-322 Api Keys documentation
- Fix FLEXIAPI-328 Set realm on Space creation, limit the update if some accounts are present
- Fix FLEXIAPI-325 Add endpoints to send the password reset and provisioning emails
- Fix FLEXIAPI-332 Check if the first line was untouched and that the number of columns is exact on each lines
- Fix FLEXIAPI-329 Use correct routes for accounts devices
- Fix FLEXIAPI-330 Remove the ConfirmedRegistration email and related code
- Fix FLEXIAPI-324 Add an app setup wizard page
- Fix FLEXIAPI-335 Safari rendering issues with font icons
- Fix FLEXIAPI-336 Fix broken ph icons
- Fix FLEXIAPI-333 Remove HTML buttons because they cannot be rendered in "old" Outlook versions
- Fix FLEXIAPI-337 Generate the provisioning URLs based on the user space
- Fix FLEXIAPI-326 Rework email templates and translations
- Fix FLEXIAPI-340 Fix the space resolution when getting the realm on Accounts
- Fix FLEXIAPI-341 Allow realm to be empty when creating a Space
- Fix FLEXIAPI-342 Enforce password change when the External Account domain is changed
- Fix FLEXIAPI-346 Complete the supporting text for the provisioning ini field
- Fix FLEXIAPI-350 Fix wrongly assigned variables in some views
- Fix FLEXIAPI-351 Fix import of CSV generated on Windows
- Fix FLEXIAPI-352 Add missing errors box in the password change form
- Fix FLEXIAPI-356 Cleanup and reorganize the pipeline to mutualize some things and save time
- Fix FLEXIAPI-355 Add withoutGlobalScope() to the Account ContactVcardList resolver
- Fix FLEXIAPI-354 Fix contact deletion
- Fix FLEXIAPI-360 Add rules on some jobs to only run them in the Gitlab pipeline when needed
- Fix FLEXIAPI-362 Return an empty object and not an empty array in the vcards-storage index endpoint to prevent some parsing issues in the clients
- Fix FLEXIAPI-312 Add Redis publish event when updating the externalAccount to ping the Flexisip B2BUA
- Fix FLEXIAPI-363 Send the Redis publish event when the externalAccount is deleted to ping the Flexisip B2BUA
- Fix FLEXIAPI-364 Fix a faulty redirection in the ExternalAccount controller
- Fix FLEXIAPI-361 Prepare the 2.0 release

v1.6
----
- Fix FLEXIAPI-192 Add DotEnv configuration to allow the expiration of tokens and codes in the app
- Fix FLEXIAPI-196 Add a phone validation system by country code with configuration panels and related tests and documentation
- Fix FLEXIAPI-203 Implement domain based Linphone configuration, add documentation, complete API endpoints, complete provisioning XML
- Fix FLEXIAPI-208 Add SMS templates documentation
- Fix FLEXIAPI-211 Add a JSON validation middleware + test
- Fix FLEXIAPI-212 Add CoTURN credentials support in the provisioning
- Fix FLEXIAPI-213 Add TURN credentials support in the API as defined in draft-uberti-behave-turn-rest-00
- Fix FLEXIAPI-216 Implement the RFC 8898 partially... for HTTP
- Fix FLEXIAPI-239 Ensure to return the correct error codes as stated in the RFC6750 section 3.1
- Fix FLEXIAPI-238 Replace Material Icons with Phosphor
- Fix FLEXIAPI-240 Update the Docker images

v1.5
---
- Fix FLEXIAPI-202 Add account parameter to the redirection in the destroy admin route
- Fix FLEXIAPI-195 Fix LiblinphoneTesterAccoutSeeder to fit with the latest Account related changes
- Fix FLEXIAPI-193 Typo
- Fix FLEXIAPI-192 Clear and upgrade properly the account dictionary entries if the entries are already existing
- Fix FLEXIAPI-191 Add quotes for the pn-prid parameter in FlexisipPusherConnector
- Fix FLEXIAPI-186 Ensure that empty objects are serialized in JSON as objects and not empty arrays
- Fix FLEXIAPI-185 Return null if the account dictionary is empty in the API
- Fix FLEXIAPI-184 Append phone_change_code and email_change_code to the admin /accounts/<id> endpoint if they are available
- Fix FLEXIAPI-183 Complete the account hooks on the dictionnary actions
- Fix FLEXIAPI-182 Replace APP_SUPER_ADMINS_SIP_DOMAINS with a proper spaces table, API endpoints, UI panels, console command, tests and documentation
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

v1.4.9
------
- Complete the missing changelog

v1.4.8
------
- Fix FLEXIAPI-166 Reimplement the deprecated email validation URL
- Fix FLEXIAPI-140 Select the display_name attribute from the database to inject...

v1.4.7
------
- Fix FLEXIAPI-175 and FLEXISIP-231 Rewrite the Redis contacts parser to handle properly SIP uris (thanks @thibault.lemaire !)

v1.4.6
------
- Fix FLEXIAPI-142 PUT /accounts endpoint doesn't allow overiding values anymore
- Fix typos and dependencies

v1.4.5
------
- Fix FLEXIAPI-132 Refactor the Provisioning to remove proxy_default_values

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

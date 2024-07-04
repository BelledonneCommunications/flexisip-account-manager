# Flexisip Account Manager Changelog

v1.5
----

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

# Flexisip Account Manager Changelog

v1.4
----
- Redesign and refactoring of the main UI and panel flows
- Removal of XMLRPC
- Add RockyLinux 9 support

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

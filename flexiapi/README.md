# FlexiAPI

FlexiAPI brings a web panel and a REST API to manage and handle Flexisip accounts related features.

## DotEnv configuration

FlexiAPI relies on [DotEnv](https://github.com/vlucas/phpdotenv) to be configured. This configuration can be accessed using the existing `.env` file that can be itself overwritten by an environnement variables.

Thoses variables can then be set using Docker-Compose, a bash script or a web-server for example.

If you're installing FlexiAPI from the RPM package you can find the configuration file at `/etc/flexisip-account-manager/flexiapi.env`.

## Manual setup

Clone the repository, install the dependencies and generate a key.

    composer install --no-dev
    php artisan key:generate

Then configure the database connection in the `.env` file (from the `.env.example` one). And migrate the tables. The migration *MUST* be run on an empty database.

    php artisan migrate

You can also run the test suit using `phpunit`.

To know more about the web server configuration part, you can directly [visit the official Laravel installation documentation](https://laravel.com/docs/8.x).

### Configure the .env file

Complete all the other variables in the `.env` file or by overwritting them in your Docker or web-server configuration:
- The OVH SMS connector
- SMTP configuration
- App name, SIP domain…

### Multi instances environement

FlexiAPI can also handle multi domains setup.

#### Multiple virtualhosts option

In your web server configuration create several virtualhosts that are pointing to the same FlexiAPI instance.
Using the environnement variables you can then configure FlexiAPI per instance.

With Apache, use the [mod_env](https://httpd.apache.org/docs/2.4/mod/mod_env.html) module.

    SetEnv APP_NAME "VirtualHost One"

On nginx use `fastcgi_param` to pass the parameter directly to PHP.

    location ~ [^/]\.php(/|$) {
        …
        include /etc/nginx/fastcgi_params;
        fastcgi_param  APP_NAME     "VirtualHost Two";
    }


> **Warning** Do not create a cache of your configuration (using `artisan config:cache`) if you have a multi-environnement setup.
> The cache is always having the priority on the variables set in the configuration files.

#### Multiple .env option

To do so, configure several web servers virtualhosts and set a specific `APP_ENV` environnement variable in each of them.

Note that if `APP_ENV` is not set FlexiAPI will directly use the default `.env` file.

FlexiAPI will then try to load a custom configuration file with the following name `.env.$APP_ENV`. So for the previous example `.env.foobar`.

You can then configure your instances with specific values.

    INSTANCE_COPYRIGHT="FooBar - Since 1997"
    INSTANCE_INTRO_REGISTRATION="Welcome on the FooBar Server"
    INSTANCE_CUSTOM_THEME=true
    …

#### Custom theme

If you set `INSTANCE_CUSTOM_THEME` to true, FlexiAPI will try to load a CSS file located in `public/css/$APP_ENV.style.css`. If the file doesn't exists it will fallback to `public/css/style.css`.

We advise you to copy the `style.css` file and rename it to make your custom CSS configurations for your instance.

#### Flexisip Push notifications pusher

The API endpoint `POST /account_creation_tokens/send-by-push` uses the `flexisip_pusher` binary delivered by the [Flexisip](https://gitlab.linphone.org/BC/public/flexisip) project (and related package). You must configure the `APP_FLEXISIP_PUSHER_PATH` environement variable to point to the correct binary.

    APP_FLEXISIP_PUSHER_PATH=/opt/belledonne-communications/bin/flexisip_pusher

This binary will be executed under "web user" privileges. Ensure that all the related files required by `flexisip_pusher` can be accessed using this user account.

    /var/opt/belledonne-communications/log/flexisip/flexisip-pusher.log // Write permissions
    /etc/flexisip/apn/*pem // Read permissions

### systemd restrictions

To retrieve the devices configuration or send SIP messages, FlexiAPI connects to UNIX sockets opened by Flexisip and the `linphone-daemon`. The sockets are located in the `/tmp` directory.
If you have issues connecting to those sockets, please ensure that your PHP process have access to it (user, rights).

The systemd service [PrivateTmp](https://access.redhat.com/blogs/766093/posts/1976243) setting might also restrict that access.

### SELinux restrictions

If you are running on a CentOS/RedHat machine, please ensure that SELinux is correctly configured.

Allow the webserver user to write in the `storage/` directory:

    chcon -R -t httpd_sys_rw_content_t storage/

Don't forget to make this change persistent if the directory may be relabeled :

    semanage fcontext -a -t httpd_sys_rw_content_t storage/

You can use the restorecon command to verify that this is working :

    restorecon storage/

If your database is located on a remote machine, you should also allow your webserver user to connect to remote hosts:

    semanage port -a -t http_port_t -p tcp 3306 // Open remote connections on the MySQL port for example
    setsebool -P httpd_can_network_connect 1 // Allow remote network connected
    setsebool -P httpd_can_network_connect_db 1 // Allow remote database connection

If you are planning to send emails using your account manager:

    setsebool -P httpd_can_sendmail 1 // Allow email to be sent

## Usage

For the web panel, a generale documentation is available under the `/documentation` page.
For the REST API, the `/api` page contains all the required documentation to authenticate and request the API.

## Console commands

FlexiAPI is shipped with several console commands that you can launch using the `artisan` executable available at the root of this project.

### Migrate an old database

FlexiAPI needs an empty database to run its migration. The following console command allow you to import simultanously an exisiting Flexisip database and the old FlexiAPI SQLite database file (if you have one) in the new one. To do so, please specify the new database configuration in the `.env` file and run the following command.

The migration script requires at least 300mb of memory to run. You should edit the `memory_limit` in the `php.ini` file to increase it.

    php artisan db:import {old_dbname} {old_sqlite_file_path?} --username={old_username} --password={old_password}

Several other parameters are also available to customize the migration process, you can list them all using the command documentation.

    php artisan -h db:import

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

This command will set the admin role to any available Flexisip account (the external Flexisip database need to be configured beforehand). You need to use the account DB id as a parameter in this command.

    php artisan accounts:set-admin {account_id}

Once one account is declared as administrator, you can directly configure the other ones using the web panel.

### Generate External Accounts

Generate `amount` accounts defined by the `group` label.
The generated accounts will have a random username suffixed by the group name.

    accounts:generate-external {amount} {group}

### Export External Accounts

Export all the accounts defined by the `group` label.
The command generates a JSON file containing the accounts ready to by imported as External Accounts in the current directory. A specific path can be defined using the `--o|output` optional parameter.

    accounts:export-to-externals {group} {--o|output=}

### Import External Accounts

Import accounts previously exported as a JSON file. Accounts previously imported will be skipped in the process.

    accounts:import-externals {file_path}

## Provisioning

FlexiAPI is providing endpoints to provision Liblinphone powered devices. You can find more documentation about it on the `/api#provisioning` documentation page.

### Provisioning hooks

The XML returned by the provisioning endpoint can be completed using hooks.

To do so, copy and rename the `provisioning_hooks.php.example` file into `provisioning_hooks.php` in the configuration directory and complete the functions in the file.
The functions already contains example codes to show you how the XML can be enhanced or completed.

### Seed liblinphone test accounts

You can also seed the tables with test accounts for the liblinphone test suite with the following command (check LiblinphoneTesterAccoutSeeder for the JSON syntax):

    php artisan accounts:seed /path/to/accounts.json

## Sending SIP messages from the API

The `POST /api/messages` endpoint allows you to send messages on the SIP network. It call internally `linphone-daemon` to do so. To be able to use it you should follow the following steps:

1. Launch the `linphone-daemon` with a UNIX socket path, this will create a socket file in `/tmp` (the file will be `/tmp/lp` for the following line).

    $ linphone-daemon --pipe ld

2. Configure the `.env` file to point to that UNIX socket

    APP_LINPHONE_DAEMON_UNIX_PATH=/tmp/ld

If you have issues connecting to that socket check the [`systemd restrictions`](#systemd-restrictions) part of this document.
# DotEnv configuration

FlexiAPI relies on [DotEnv](https://github.com/vlucas/phpdotenv) to be configured. This configuration can be accessed using the existing `.env` file that can be itself overwritten by an environnement variables.

Thoses variables can then be set using Docker-Compose, a bash script or a web-server.

If you're installing FlexiAPI from the RPM package you can find the configuration file at `/etc/flexisip-account-manager/flexiapi.env`.

# 1.a Manual setup

Clone the repository, install the dependencies and generate a key.

    composer install --no-dev
    php artisan key:generate

Then configure the database connection in the `.env` file (from the `.env.example` one). And migrate the tables. The first migration *MUST* be run on an empty database.

    php artisan migrate

You can also run the test suit using `phpunit`.

To know more about the web server configuration part, you can directly [visit the official Laravel installation documentation](https://laravel.com/docs/).

# 1.b Packages setup

FlexiAPI is packaged for Debian and RedHat, you can setup those repositories using the Flexisip documentation https://wiki.linphone.org/xwiki/wiki/public/view/Flexisip/1.%20Installation/#HInstallationfromourrepositories

    yum install bc-flexisip-account-manager # For RedHat distributions
    apt install bc-flexisip-account-manager # For Debian distributions

# 2. Web server configuration

The package will deploy a `flexisip-account-manager.conf` file in the apache2 configuration directory.
This file can be loaded and configured in your specific VirtualHost configuration.

# 3. .env file configuration

Complete all the other variables in the `.env` file or by overwritting them in your Docker or web-server configuration.

## 3.1. Mandatory `APP_ROOT_HOST` variable

`APP_ROOT_HOST` contains the HTTP host where your FlexiAPI is hosted (eg. `flexiapi.domain.tld` or directly `flexiapi-domain.tld`).

This is the host that you'll define in the Apache or webserver VirtualHost:

    ServerName flexiapi-domain.tld
    ServerAlias *.flexiapi-domain.tld

If you are planning to manage several SIP domains (see Spaces bellow) a wildcard `ServerAlias` as above is required.

## 3.2. For manual setups

`APP_KEY` Can be set using the `php artisan key:generate` command.

# 4. Spaces

Since the 1.6 FlexiAPI can manage different SIP Domains on separate HTTP subdomains.

A Space is defined as a specific HTTP subdomain of `APP_ROOT_HOST` and is linked to a specific SIP Domain. It is also possible to host one specific Space directly under `APP_ROOT_HOST`.

By default administrator accounts in Spaces will only see the accounts of their own Space (that have the same SIP Domain).
However it is possible to define a Space as a "SuperSpace" allowing the admins to see all the other Spaces and accounts and create/edit/delete the other Spaces.

## 4.1. Setup the first Space

You will need to create the first Space manually, generally as a SuperSpace, after that the other Spaces can directly be created in your browser through the web panel.

    php artisan spaces:create-update {sip_domain} {host} {--super}

For example:

    php artisan spaces:create-update company-sip-domain.tld flexiapi-domain.tld --super
    php artisan spaces:create-update other-sip-domain.tld other.flexiapi-domain.tld

## 5. Create a first administrator and finish the setup

Create a first administator account:

    php artisan accounts:create-admin-account {-u|username=} {-p|password=} {-d|domain=}

For example:

    php artisan accounts:create-admin-account admin strong_password my-company-sip-domain.tld

You can now try to authenticate on the web panel and continue the setup using your admin account.

# Other custom configurations

## Multiple virtualhosts option

In your web server configuration create several VirtualHosts that are pointing to the same FlexiAPI instance.
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

## Multiple .env option

To do so, configure several web servers virtualhosts and set a specific `APP_ENV` environnement variable in each of them.

Note that if `APP_ENV` is not set FlexiAPI will directly use the default `.env` file.

FlexiAPI will then try to load a custom configuration file with the following name `.env.$APP_ENV`. So for the previous example `.env.foobar`.

You can then configure your instances with specific values.

    INSTANCE_COPYRIGHT="FooBar - Since 1997"
    INSTANCE_INTRO_REGISTRATION="Welcome on the FooBar Server"
    INSTANCE_CUSTOM_THEME=true
    …

## Custom theme

If you set `INSTANCE_CUSTOM_THEME` to true, FlexiAPI will try to load a CSS file located in `public/css/$APP_ENV.style.css`. If the file doesn't exists it will fallback to `public/css/style.css`.

You can find an example CSS file at `public/css/custom.style.css`.

## Flexisip Push notifications pusher

The API endpoint `POST /account_creation_tokens/send-by-push` uses the `flexisip_pusher` binary delivered by the [Flexisip](https://gitlab.linphone.org/BC/public/flexisip) project (and related package). You must configure the `APP_FLEXISIP_PUSHER_PATH` and `APP_FLEXISIP_PUSHER_FIREBASE_KEYSMAP` environnement variables to point to the correct binary.

    APP_FLEXISIP_PUSHER_PATH=/opt/belledonne-communications/bin/flexisip_pusher

This binary will be executed under "web user" privileges. Ensure that all the related files required by `flexisip_pusher` can be accessed using this user account.

    /var/opt/belledonne-communications/log/flexisip/flexisip-pusher.log // Write permissions
    /etc/flexisip/apn/*pem // Read permissions

## SELinux restrictions

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

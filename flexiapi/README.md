# FlexiAPI

This tool connects to the Flexisip CLI interface and exposes several endpoints to request and manage it.

## DotEnv configuration

FlexiAPI relies on [DotEnv](https://github.com/vlucas/phpdotenv) to be configured. This configuration can be accessed using the existing `.env` file that can be itself overwritten by an environnement variables.

Thoses variables can then be set using Docker-Compose, a bash script or a web-server for example.

If you're installing FlexiAPI from the RPM package you can find the configuration file at `/etc/flexisip-account-manager/flexiapi.env`.

## Manual setup

Clone the repository, install the dependencies and generate a key.

    composer install --no-dev
    php artisan key:generate

Then configure the two databases connections in the `.env` file (from the `.env.example` one). And migrate the tables.

    php artisan migrate

The local one (that is by default using SQLite) is used to store authentications sessions. The remote one should be configured to connect directly to the Flexisip database.

You can also run the test suit using `phpunit`.

To know more about the web server configuration part, you can directly [visit the official Laravel installation documentation](https://laravel.com/docs/8.x).

### Configure the .env file

Complete all the other variables in the `.env` file or by overwritting them in your Docker or web-server configuration:
- The OVH SMS connector
- SMTP configuration
- App name, SIP domain…

### Multi instances environement

FlexiAPI can also handle multi domains setup.

To do so, configure several web servers virtualhosts and set a specific `APP_ENV` environement variable in each of them.

With Apache, use the [mod_env](https://httpd.apache.org/docs/2.4/mod/mod_env.html) module.

    SetEnv APP_ENV foobar

On nginx use `fastcgi_param` to pass the parameter directly to PHP.

    location ~ [^/]\.php(/|$) {
        …
        include /etc/nginx/fastcgi_params;
        fastcgi_param  APP_ENV     foobar;
    }

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

### systemd restrictions

To retrieve the devices configuration, FlexiAPI connects to the UNIX socket opened by Flexisip. The socket is located in the `/tmp` directory.
If you have issues connecting to that socket, please ensure that your PHP process have access to it (user, rights).

The systemd service [PrivateTmp](https://access.redhat.com/blogs/766093/posts/1976243) setting might restrict that access.

### SELinux restrictions

If you are running on a CentOS/RedHat machine, please ensure that SELinux is correctly configured.

Allow the webserver user to write in the `storage/` directory:

    chcon -R -t httpd_sys_rw_content_t storage/

If you have your SQLite DB setup in another directory don't forget to allow write rights as well

    chcon -R -t httpd_sys_rw_content_t db.sqlite

If your external database is locate on a remote machine, you should also allow your webserver user to connect to remote hosts:

    semanage port -a -t http_port_t -p tcp 3306 // Open remote connections on the MySQL port for example
    setsebool httpd_can_network_connect 1 // Allow remote network connected
    setsebool httpd_can_network_connect_db 1 // Allow remote database connection

## Usage

The `/api` page contains all the required documentation to authenticate and request the API.

## Console commands

FlexiAPI is shipped with several console commands that you can launch using the `artisan` executable available at the root of this project.

### Clear Expired Nonces for DIGEST authentication

This will remove the nonces stored that were not updated after `x minutes`.

    php artisan digest:expired-nonces-clear {minutes}

### Remove the unconfirmed accounts

This request will remove the accounts that were not confirmed after `x days`. In the database an unconfirmed account is having the `activated` attribute set to `false`.

    php artisan accounts:clear-unconfirmed {days} {--apply}

The base request will not delete the related accounts by default. You need to add `--apply` to remove them.

### Set an account admin

This command will set the admin role to any available FlexiSIP account (the external FlexiSIP database need to be configured beforehand). You need to use the account DB id as a parameter in this command.

    php artisan accounts:set-admin {account_id}

Once one account is declared as administrator, you can directly configure the other ones using the web panel.

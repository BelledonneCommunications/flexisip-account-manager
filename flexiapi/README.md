# FlexiAPI

This tool connects to the Flexisip CLI interface and exposes several endpoints to request and manage it.

## Setup

Clone the repository, install the dependencies and generate a key.

    composer install --no-dev
    php artisan key:generate

Then configure the two databases connections in the `.env` file (from the `.env.example` one). And migrate the tables.

    php artisan migrate

The local one (that is by default using SQLite) is used to store authentications sessions. The remote one should be configured to connect directly to the Flexisip database.

You can also run the test suit using `phpunit`.

To know more about the web server configuration part, you can directly [visit the official Laravel installation documentation](https://laravel.com/docs/6.x).

### Configure the .env file

Complete all the other variables in the `.env` file:
- The OVH SMS connector
- SMTP configuration
- App name, SIP domain…

### SELinux

If you are running on a CentOS/RedHat machine, please ensure that SELinux is correctly configured.

Allow the webserver user to write in the `storage/` directory:

    chcon -R -t httpd_sys_rw_content_t storage/

If your external database is locate on a remote machine, you should also allow your webserver user to connect to remote hosts:

    semanage port -a -t http_port_t -p tcp 3306 // Open remote connections on the MySQL port for example
    setsebool httpd_can_network_connect 1 // Allow remote network connected
    setsebool httpd_can_network_connect_db 1 // Allow remote database connection

### CRON job

The DIGEST authentication method is saving some temporary information (nonces) in the database.

To expire and/or clear old nonces a specific command should be called periodically.

    php artisan digest:expired-nonces-clear <minutes>

## Usage

The root page contains all the required documentation to authenticate and request the API.

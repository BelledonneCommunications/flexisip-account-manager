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

### CRON job

The DIGEST authentication method is saving some temporary information (nonces) in the database.

To expire and/or clear old nonces a specific command should be called periodically.

    php artisan digest:expired-nonces-clear <minutes>

## Usage

The root page contains all the required documentation to authenticate and request the API.

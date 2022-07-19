## Introduction

Flexisip Account Manager is a software product running on a server, dedicated to the creation and management of SIP accounts from Linphone-based apps.
It supports user identity validation via email or SMS, secure user authentication with SHA-256 digest or TLS client certificates.

Flexisip Account Manager also includes a remote provisioning server for static auto-configuration, compatible with Linphone URL / QR Code provisioning feature.

## License

Flexisip Account Manager is dual licensed, and can be licensed and distributed:
* under an Affero GPLv3 license for free (see LICENSE.txt file for details)
* under a proprietary license, for closed source projects. Contact [Belledonne Communications](https://www.linphone.org/contact) for any question about costs and services.

## Documentation

### Install RPM package with dependencies

Enable Belledonne Communications repository:

```bash
cat > /etc/yum.repos.d/Belledonne.repo << EOF
[Belledonne]
name=Belledonne
baseurl=https://www.linphone.org/snapshots/centos7/
enabled=1
gpgcheck=0
EOF
```

CentOS-SCLo-scl-rh repository is also required for PHP 7.3:

```bash
yum install centos-release-scl-rh
```

RPM package should install necessary dependencies automatically.

```bash
yum install bc-flexisip-account-manager
```

This package depends on `rh-php73` which will be installed in `/opt/rh/rh-php73/`.
If you don't have any other php installed on your server, use the following to be able to use php commands:

```bash
ln -s /opt/rh/rh-php73/root/usr/bin/php /usr/bin/php
```

### Apache server configuration

The RPM will create a `flexisip-account-manager.conf` file inside `/opt/rh/httpd24/root/etc/httpd/conf.d/`

It simply contains an Alias directive, up to you to configure your virtual host correctly.

Once you're done, reload the configuration inside httpd: `service httpd24-httpd reload`

### MySQL database configuration

For the account manager to work, you need a mysql database with a user that has read/write access.

### XMLRPC server configuration

The RPM package has installed the configuration files in `/etc/flexisip-account-manager/`

Each file name should be explicit on which settings it contains. If you have any doubt, leave the default value.
At least you MUST edit the following file and fill the values you used in previous step:

```bash
nano /etc/flexisip-account-manager/db.conf
```

To create the database schema, use the `artisan migrate` script provided by FlexiAPI.

### Email configuration

Flexisip Account Manager is sending email to allow the accounts activations. To allow emails to be sent a few configuration steps are required:

1. Install `sendmail` and `postfix`
2. Set `EMAIL_ENABLED` and `SEND_ACTIVATION_EMAIL` to `true` in the configuration
3. Ensure that postfix is correctly configured (regarding the `relayhost` setting in `main.cf` for example)

### Install OVH SMS gateway dependency (optionnal)

Download and install [composer](https://getcomposer.org/download/) or use the one already provided by your OS.
Then install the `php-ovh-sms` library in the `flexisip-account-manager` directory.

    cd /opt/belledonne-communications/share/flexisip-account-manager/
    php composer.phar require ovh/php-ovh-sms

### Packaging

To build a rpm package on centos7:

```bash
make rpm
```

To build a rpm package with docker:

```bash
docker run -v $PWD:/home/bc -it gitlab.linphone.org:4567/bc/public/flexisip-account-manager/bc-dev-centos:7 make rpm
```

GitLab is running the command above using `make rpm-dev`, this also install all the required dependencies to run `phpunit` properly (they are disabled by default to save space in the final rpm file).

The flexisip-account-manager rpm package can be found in `rpmbuild/RPMS/x86_64/bc-flexisip-account-manager*.rpm`

### Miscellaneous

- For remote provisioning create a `default.rc` file in `/opt/belledonne-communications/` and set the values you want
client side, set the provisioning uri to the same host but to `provisioning.php` instead of `xmlrpc.php`.

- If SELinux forbids mail sending you can try this command:
`setsebool -P httpd_can_sendmail=1`

- On CentOS firewalld might be running:
`firewall-cmd --state`

- If it is running you can add a rule to allow https traffic:
`firewall-cmd --zone public --permanent --add-port=444/tcp && firewall-cmd --reload`

- If you use the standard https port (443) or http (80) the following command might be better:
`firewall-cmd --zone public --permanent --add-service={http,https} && firewall-cmd --reload`

- Also it can listen on IPv6 only.
To fix that, edit `/opt/rh/httpd24/root/etc/httpd/conf.d/ssl.conf` and add/set: `Listen 0.0.0.0:444 https`

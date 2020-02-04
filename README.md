### Introduction

Flexisip Account Manager is a software product running on a server, dedicated to the creation and management of SIP accounts from Linphone-based apps.
It supports user identity validation via email or SMS, secure user authentication with SHA-256 digest or TLS client certificates.

Flexisip Account Manager also includes a remote provisioning server for static auto-configuration, compatible with Linphone URL / QR Code provisioning feature.

### License

Flexisip Account Manager is dual licensed, and can be licensed and distributed:
* under an Affero GPLv3 license for free (see LICENSE.txt file for details)
* under a proprietary license, for closed source projects. Contact [Belledonne Communications](https://www.linphone.org/contact) for any question about costs and services.

### 1. Install RPM package with dependencies
--------------------------------------------

RPM package should install necessary dependencies automatically.

`yum install bc-flexisip-account-manager`

This package depends on `rh-php71` which will be installed in `/opt/rh/rh-php71/`.
If you don't have any other php installed on your server, use the following to be able to use php commands:

`ln -s /opt/rh/rh-php71/root/usr/bin/php /usr/bin/php`

### 2. Configure Apache server
------------------------------

The RPM will create a `flexisip-account-manager.conf` file inside `/opt/rh/httpd24/root/etc/httpd/conf.d/`

It simply contains an Alias directive, up to you to configure your virtual host correctly.

Once you're done, reload the configuration inside httpd: `service httpd24-httpd reload`

### 3. Install and setup MySQL database
---------------------------------------

For the account manager to work, you need a mysql database with a user that has read/write access.

### 4. Configure XMLRPC server
------------------------------

The RPM package has installed the configuration files in `/etc/flexisip-account-manager/`

Each file name should be explicit on which settings it contains. If you have any doubt, leave the default value.
At least you MUST edit the following file and fill the values you used in previous step:

`nano /etc/flexisip-account-manager/db.conf`

Now you can create the necessary tables in the database using our script:

`php /opt/belledonne-communications/share/flexisip-account-manager/tools/create_tables.php`

### 5. Install OVH SMS gateway dependency (optionnal)

To install OVH SMS PHP API create a `composer.json` file in `/opt/belledonne-communications/`:

`echo '{ "name": "XMLRPC SMS API", "description": "XMLRPC SMS API", "require": { "ovh/php-ovh-sms": "dev-master" } }' > /opt/belledonne-communications/share/flexisip-account-manager/composer.json`

Then download and install [composer](https://getcomposer.org/download/).

Finally start composer:

`cd /opt/belledonne-communications/share/flexisip-account-manager/ && composer install`

### 6. Packaging
--------------------
To build a rpm package on centos7:
make rpm
To build a rpm package with docker:
docker run -v $PWD:/home/bc -it gitlab.linphone.org:4567/bc/public/flexisip-account-manager/bc-dev-centos:7 make rpm
The flexisip-account-manager rpm package can be found in rpmbuild/RPMS/x86_64/bc-flexisip-account-manager*.rpm
Installation requires package centos-release-scl-rh to be installed for php7.1

### 7. Miscellaneous
--------------------

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


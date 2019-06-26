### 1. Install RPM package with dependencies
--------------------------------------------

# RPM package should install necessary dependencies automatically
# Check that the PHP version is 5.4 or higher

yum install flexisip-account-manager-1.0-1.0.x86_64.rpm

### 2. Configure Apache server
------------------------------

# Edit factory apache configuration file and replace the following parameters with the correct values:
# ServerName, ServerAdmin, ErrorLog, CustomLog, SSLCertificateFile, SSLCertificateKeyFile
# Copy this file to the configuration folder of the apache server with a new name

cp /etc/flexisip-account-manager/apache.conf /etc/httpd/conf.d/flexisip-account-manager.conf

# If your apache server is brand new you might need to add a ServerName in httpd.conf
# Start the apache server with the root user

systemctl start httpd

# If the httpd service doesn't start properly it might be a log folder permission issue
# Check that httpd can write logs in destination folder, if not you can use /var/log/httpd

### 3. Install and setup MySQL database
---------------------------------------

# Install the mariadb-server package and start the mariadb service

yum install mariadb-server
systemctl start mariadb

# Configure the newly installed mariadb server
# When asked for root password press Enter and create a new root password

mysql_secure_installation

# Create a database and a user with the rights to read and write
# Replace <user> and <password> in the following command

mysql -u root -p
create database flexisip;
grant all on flexisip.* to <username>@'localhost' identified by '<password>';
flush privileges;
exit

### 4. Configure XMLRPC server
------------------------------

# The RPM package has installed XMLRPC configuration files in /etc/flexisip-account-manager/
# Edit these files with the correct values

vim /etc/flexisip-account-manager/xmlrpc.conf
vim /etc/flexisip-account-manager/internationalization.conf

# Create the necessary tables in the database using our script

cd /opt/belledonne-communications/share/flexisip-account-manager
php xmlrpc.php create_tables
php xmlrpc.php create_algo_table

# For remote provisioning create a default.rc file on /opt/belledonne-communications/ and set the values you want
# Client side, set the provisioning uri to the same host but to provisioning.php instead of xmlrpc.php

### 5. Miscellaneous
--------------------

# To install OVH SMS PHP API create composer.json in /opt/belledonne-communications/

echo '{ "name": "XMLRPC SMS API", "description": "XMLRPC SMS API", "require": { "ovh/php-ovh-sms": "dev-master" } }' > /var/www/html/composer.json

# Then execute the following command

cd /opt/belledonne-communications && composer install

# If you have not installed an OVH SMS API you might need to comment out the following lines in xmlrpc-sms.php

require __DIR__ . '/vendor/autoload.php';
use \Ovh\Sms\SmsApi;

# if SELinux forbids mail sending you can try this command

setsebool -P httpd_can_sendmail=1

# On CentOS firewalld might be running:
firewall-cmd --state

# If it is running you can add a rule to allow https traffic
firewall-cmd --zone public --permanent --add-port=444/tcp && firewall-cmd --reload

# If you use the standard https port (443) or http (80) the following command might be better
firewall-cmd --zone public --permanent --add-service={http,https} && firewall-cmd --reload

# Also it can listen on IPv6 only
# To fix that, edit the ssl.conf in /etc/httpd/conf.d/ dir and add/set: Listen 0.0.0.0:444 https


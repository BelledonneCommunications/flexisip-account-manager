# -*- rpm-spec -*-

%define build_number MAKE_FILE_BUILD_NUMBER_SEARCH
%define var_dir /var/opt/belledonne-communications
%define opt_dir /opt/belledonne-communications/share/flexisip-account-manager

%if %{with deb}
    %define env_config_file "/etc/flexisip-account-manager/flexiapi.env"
%else
    %define env_config_file "$RPM_BUILD_ROOT/etc/flexisip-account-manager/flexiapi.env"
%endif

%define env_symlink_file %{opt_dir}/flexiapi/.env

%bcond_with deb

%if %{with deb}
    %define web_user www-data
    %define apache_conf_path /etc/apache2/conf-available
%else
    %define web_user apache
%endif

Name:           bc-flexisip-account-manager
Version:        MAKE_FILE_VERSION_SEARCH
Release:        %{build_number}%{?dist}
Summary:        Web panel and a REST API to manage and handle Flexisip accounts related features. Only tested for Apache2.

Group:          Applications/Communications
License:        GPL
URL:            http://www.linphone.org
Source0:        flexisip-account-manager.tar.gz

#These are not indented because rpm cannot recognize "Requires" with spaces/tabs (???)

%if "%{?dist}" == ".el7"
Requires:       rh-php73-php rh-php73-php-gd rh-php73-php-xmlrpc rh-php73-php-pdo rh-php73-php-mysqlnd rh-php73-php-mbstring
%define apache_conf_path /opt/rh/httpd24/root/etc/httpd/conf.d
%else
Requires:       php php-gd php-xmlrpc php-pdo php-mysqlnd php-mbstring
%define apache_conf_path /etc/httpd/conf.d
%endif

%description
PHP server for Linphone and Flexisip providing module for account creation.

%prep
%setup -n flexisip-account-manager

%install
rm -rf "$RPM_BUILD_ROOT"
mkdir -p "$RPM_BUILD_ROOT%{opt_dir}"
cp -R xmlrpc/src/* "$RPM_BUILD_ROOT%{opt_dir}/"

cp -R flexiapi "$RPM_BUILD_ROOT%{opt_dir}"
cp flexiapi/composer.json "$RPM_BUILD_ROOT%{opt_dir}/flexiapi"

cp README* "$RPM_BUILD_ROOT%{opt_dir}/"
mkdir -p "$RPM_BUILD_ROOT/etc/flexisip-account-manager"
cp -R conf/* "$RPM_BUILD_ROOT/etc/flexisip-account-manager/"

mkdir -p $RPM_BUILD_ROOT/etc/cron.daily

mkdir -p $RPM_BUILD_ROOT%{apache_conf_path}
cp httpd/flexisip-account-manager.conf "$RPM_BUILD_ROOT%{apache_conf_path}/"

%if %{with deb}
    cp cron/flexiapi.debian "$RPM_BUILD_ROOT/etc/cron.daily/"
    chmod +x "$RPM_BUILD_ROOT/etc/cron.daily/flexiapi.debian"
%else
    cp cron/flexiapi.redhat "$RPM_BUILD_ROOT/etc/cron.daily/"
    chmod +x "$RPM_BUILD_ROOT/etc/cron.daily/flexiapi.redhat"
%endif

# POST INSTALLATION

%post
%if %{without deb}
    if [ $1 -eq 1 ] ; then
%endif

    # Create the var directory if it doesn't exists

    # FlexiAPI logs file
    mkdir -p %{var_dir}/log/flexiapi

    # FlexiAPI base directories setup and rights
    mkdir -p %{var_dir}/flexiapi/storage/app/public
    mkdir -p %{var_dir}/flexiapi/storage/framework/cache/data
    mkdir -p %{var_dir}/flexiapi/storage/framework/sessions
    mkdir -p %{var_dir}/flexiapi/storage/framework/testing
    mkdir -p %{var_dir}/flexiapi/storage/framework/views
    mkdir -p %{var_dir}/flexiapi/bootstrap/cache

    mkdir -p %{var_dir}/log
    touch %{var_dir}/log/account-manager.log
    chown %{web_user}:%{web_user} %{var_dir}/log/account-manager.log

%if %{without deb}
    chcon -t httpd_sys_rw_content_t %{var_dir}/log/account-manager.log
%endif

%if %{without deb}
    setsebool -P httpd_can_network_connect_db on
%endif

    chown -R %{web_user}:%{web_user} %{var_dir}/log
    chown -R %{web_user}:%{web_user} %{var_dir}/flexiapi/storage
    chown -R %{web_user}:%{web_user} %{var_dir}/flexiapi/bootstrap
    chown -R %{web_user}:%{web_user} %{var_dir}/log/flexiapi

    # Forces the creation of the symbolic links event if they already exists
    ln -sf %{var_dir}/log/flexiapi %{var_dir}/flexiapi/storage/logs
    ln -sf %{var_dir}/flexiapi/storage %{opt_dir}/flexiapi/.

    # FlexiAPI env file configuration
    if ! test -f %{env_config_file}; then
        cd %{opt_dir}/flexiapi/
        cp .env.example %{env_config_file}

        ln -s %{env_config_file} %{env_symlink_file}

        %if %{with deb}
            php artisan key:generate
        %else
            scl enable rh-php73 "php artisan key:generate"
        %endif
    fi

    # Link it once more
    ln -sf %{env_config_file} %{env_symlink_file}

    # Check if there is a migration
    if cd %{opt_dir}/flexiapi/ && php artisan migrate:status | grep -q No; then
        echo " "
        echo "All the following commands need to be run with the web user"
        echo "sudo -su %{web_user}"
        echo "You need to migrate the database to finish the setup:"
        echo "%{web_user}$ cd %{opt_dir}/flexiapi/"

        %if %{with deb}
            echo %{web_user}$ php artisan migrate
        %else
            echo %{web_user}$ scl enable rh-php73 "php artisan migrate"
        %endif
    fi

%if %{without deb}
fi
%endif

# FILES

%files
%{opt_dir}/flexiapi/
%{opt_dir}/api/account/*.php
%{opt_dir}/config/*.php
%{opt_dir}/database/*.php
%{opt_dir}/misc/*.php
%{opt_dir}/objects/*.php
%{opt_dir}/tools/*.php
%{opt_dir}/xmlrpc/*.php
%{opt_dir}/README*

%exclude %{opt_dir}/flexiapi/storage/

%config(noreplace) /etc/flexisip-account-manager/*.conf

%config(noreplace) %{apache_conf_path}/flexisip-account-manager.conf
%if %{with deb}
    %config(noreplace) /etc/cron.daily/flexiapi.debian
%else
    %config(noreplace) /etc/cron.daily/flexiapi.redhat
%endif

%clean
rm -rf $RPM_BUILD_ROOT

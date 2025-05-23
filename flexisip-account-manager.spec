# -*- rpm-spec -*-

%define build_number MAKE_FILE_BUILD_NUMBER_SEARCH
%define var_dir /var/opt/belledonne-communications
%define opt_dir /opt/belledonne-communications/share/flexisip-account-manager
%define etc_dir /etc/flexisip-account-manager

%if %{with deb}
    %define env_config_file %{etc_dir}/flexiapi.env
%else
    %define env_config_file "$RPM_BUILD_ROOT%{etc_dir}/flexiapi.env"
%endif


%define env_symlink_file %{opt_dir}/flexiapi/.env

%bcond_with deb

%if %{with deb}
    %define web_user www-data
    %define apache_conf_path /etc/apache2/conf-available
%else
    %define web_user apache
    %define apache_conf_path /etc/httpd/conf.d
%endif

Name:           bc-flexisip-account-manager
Version:        MAKE_FILE_VERSION_SEARCH
Release:        %{build_number}%{?dist}
Summary:        Web panel and a REST API to manage and handle Flexisip accounts related features. Only tested for Apache2.

Group:          Applications/Communications
License:        GPL
URL:            http://www.linphone.org
Source0:        flexisip-account-manager.tar.gz

Requires:       php >= 8.1, php-gd, php-pdo, php-redis, php-mysqlnd, php-mbstring

%description
PHP server for Linphone and Flexisip providing module for account creation.

%prep
%setup -n flexisip-account-manager

%install
rm -rf "$RPM_BUILD_ROOT"
mkdir -p "$RPM_BUILD_ROOT%{opt_dir}"

cp -R flexiapi "$RPM_BUILD_ROOT%{opt_dir}"
cp flexiapi/composer.json "$RPM_BUILD_ROOT%{opt_dir}/flexiapi"

cp README* "$RPM_BUILD_ROOT%{opt_dir}/"
cp INSTALL* "$RPM_BUILD_ROOT%{opt_dir}/"
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

%posttrans
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

chown -R %{web_user}:%{web_user} %{var_dir}/log
chown -R %{web_user}:%{web_user} %{var_dir}/flexiapi/storage
chown -R %{web_user}:%{web_user} %{var_dir}/flexiapi/bootstrap
chown -R %{web_user}:%{web_user} %{var_dir}/log/flexiapi

# Forces the creation of the symbolic links event if they already exists
ln -sf %{var_dir}/log/flexiapi %{var_dir}/flexiapi/storage/logs
ln -sf %{var_dir}/flexiapi/storage %{opt_dir}/flexiapi/.

# if selinux is installed on the system (even if not enabled)
which setsebool > /dev/null 2>&1
if [ $? -eq 0 ] ; then
    setsebool -P httpd_can_network_connect on
    setsebool -P httpd_can_network_connect_db on
    setsebool -P httpd_can_sendmail on

    semanage fcontext -a -t var_log_t %{var_dir}/log 2>/dev/null || :
    semanage fcontext -a -t httpd_log_t "%{var_dir}/log/flexiapi(./*)?" 2>/dev/null || :
    restorecon %{var_dir}/log || :
    restorecon -R %{var_dir}/log/flexiapi || :

    semanage fcontext -a -t httpd_sys_rw_content_t "%{var_dir}/flexiapi/storage(/.*)?" 2>/dev/null || :
    restorecon -R %{var_dir}/flexiapi/storage || :

    semanage fcontext -a -t httpd_sys_rw_content_t "%{opt_dir}/flexiapi/storage(/.*)?" 2>/dev/null || :
    restorecon -R %{opt_dir}/flexiapi/storage || :
fi

# FlexiAPI env file configuration
if ! test -f %{env_config_file}; then
    cd %{opt_dir}/flexiapi/
    mkdir -p %{etc_dir}
    cp -R .env.example %{env_config_file}
    ln -s %{env_config_file} %{env_symlink_file}

    php artisan key:generate
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
    echo %{web_user}$ php artisan migrate
fi


%postun
# Final removal.
# if selinux is installed on the system (even if not enabled)
which setsebool > /dev/null 2>&1
if [ $? -eq 0 ] ; then
    semanage fcontext -d -t httpd_log_t "%{var_dir}/log/flexiapi(./*)?" 2>/dev/null || :
    semanage fcontext -d -t httpd_sys_rw_content_t "%{var_dir}/flexiapi/storage(/.*)?" 2>/dev/null || :
    semanage fcontext -d -t httpd_sys_rw_content_t "%{opt_dir}/flexiapi/storage(/.*)?" 2>/dev/null || :
    if [ $1 -eq 0 ] ; then
        restorecon -R %{var_dir}/log/flexiapi || :
        restorecon -R %{var_dir}/flexiapi/storage || :
        restorecon -R %{opt_dir}/flexiapi/storage || :
    fi
fi


# FILES

%files
%{opt_dir}/flexiapi/
%{opt_dir}/README*
%{opt_dir}/INSTALL*

%exclude %{opt_dir}/flexiapi/storage/

%config(noreplace) %{apache_conf_path}/flexisip-account-manager.conf
%if %{with deb}
    %config(noreplace) /etc/cron.daily/flexiapi.debian
%else
    %config(noreplace) /etc/cron.daily/flexiapi.redhat
%endif

%clean
rm -rf $RPM_BUILD_ROOT

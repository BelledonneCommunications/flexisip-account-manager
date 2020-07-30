# -*- rpm-spec -*-
#%define _prefix    @CMAKE_INSTALL_PREFIX@
#%define pkg_prefix @BC_PACKAGE_NAME_PREFIX@

# re-define some directories for older RPMBuild versions which don't. This messes up the doc/ dir
# taken from https://fedoraproject.org/wiki/Packaging:RPMMacros?rd=Packaging/RPMMacros
#%define _datarootdir       %{_prefix}/share
#%define _datadir           %{_datarootdir}
#%define _docdir            %{_datadir}/doc

%define build_number 24
%define var_dir /var/opt/belledonne-communications
%define opt_dir /opt/belledonne-communications/share/flexisip-account-manager
%define env_file "$RPM_BUILD_ROOT/etc/flexisip-account-manager/flexiapi.env"
#%if %{build_number}
#%define build_number_ext -%{build_number}
#%endif

Name:           bc-flexisip-account-manager
Version:        1.1.0
Release:        %{build_number}%{?dist}
Summary:        SIP account management xml-rpc server, for use with flexisip server suite.

Group:          Applications/Communications
License:        GPL
URL:            http://www.linphone.org
#Source0:        %{name}-%{version}%{?build_number_ext}.tar.gz
Source0:        flexisip-account-manager.tar.gz
#BuildRoot:      %{_tmppath}/%{name}-%{version}-%{release}-buildroot

# dependencies
Requires:       rh-php73-php rh-php73-php-xmlrpc rh-php73-php-pdo rh-php73-php-mysqlnd rh-php73-php-mbstring

%description
PHP server for Linphone and Flexisip providing module for account creation.


%prep
%setup -n flexisip-account-manager

%install
rm -rf "$RPM_BUILD_ROOT"
mkdir -p "$RPM_BUILD_ROOT%{opt_dir}"
cp -R src/* "$RPM_BUILD_ROOT%{opt_dir}/"

cp -R flexiapi "$RPM_BUILD_ROOT%{opt_dir}"
cp flexiapi/composer.json "$RPM_BUILD_ROOT%{opt_dir}/flexiapi"

cp README* "$RPM_BUILD_ROOT%{opt_dir}/"
mkdir -p "$RPM_BUILD_ROOT/etc/flexisip-account-manager"
cp -R conf/* "$RPM_BUILD_ROOT/etc/flexisip-account-manager/"
mkdir -p $RPM_BUILD_ROOT/opt/rh/httpd24/root/etc/httpd/conf.d
cp httpd/flexisip-account-manager.conf "$RPM_BUILD_ROOT/opt/rh/httpd24/root/etc/httpd/conf.d/"


%post
if [ $1 -eq 1 ] ; then
    mkdir -p %{var_dir}/log
    touch %{var_dir}/log/account-manager.log
    chown apache:apache %{var_dir}/log/account-manager.log
    chcon -t httpd_sys_rw_content_t %{var_dir}/log/account-manager.log
    setsebool -P httpd_can_network_connect_db on

    # FlexiAPI base directories setup and rights
    mkdir -p %{var_dir}/flexiapi/storage/app/public
    mkdir -p %{var_dir}/flexiapi/storage/framework/cache %{var_dir}/flexiapi/storage/framework/sessions %{var_dir}/flexiapi/storage/framework/testing %{var_dir}/flexiapi/storage/framework/views
    mkdir -p %{opt_dir}/flexiapi/bootstrap/cache
    touch %{var_dir}/flexiapi/storage/db.sqlite
    touch %{var_dir}/flexiapi/storage/external.db.sqlite
    chown -R apache:apache %{var_dir}/flexiapi/storage

    ln -s %{var_dir}/flexiapi/storage %{opt_dir}/flexiapi/.

    # FlexiAPI logs file
    mkdir -p %{var_dir}/log/flexiapi
    chown -R apache:apache %{var_dir}/log/flexiapi

    ln -s %{var_dir}/log/flexiapi %{opt_dir}/flexiapi/storage/logs

    # FlexiAPI env file configuration
    cd %{opt_dir}/flexiapi/
    cp .env.example %{env_file}
    sed -i 's/DB_DATABASE=.*/DB_DATABASE=\/var\/opt\/belledonne-communications\/flexiapi\/storage\/db.sqlite/g' %{env_file}
    sed -i 's/DB_EXTERNAL_DRIVER=.*/DB_EXTERNAL_DRIVER=sqlite/g' %{env_file}
    sed -i 's/DB_EXTERNAL_DATABASE=.*/DB_EXTERNAL_DATABASE=\/var\/opt\/belledonne-communications\/flexiapi\/storage\/external.db.sqlite/g' %{env_file}

    ln -s %{env_file} .env

    scl enable rh-php73 "php artisan key:generate"
    scl enable rh-php73 "php artisan migrate"
fi


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
%exclude %{opt_dir}/flexiapi/bootstrap/cache

%config(noreplace) /etc/flexisip-account-manager/*.conf
%config(noreplace) /opt/rh/httpd24/root/etc/httpd/conf.d/flexisip-account-manager.conf

%clean
rm -rf $RPM_BUILD_ROOT

%changelog
* Tue Jan 5 2020 Timothée Jaussoin <timothee.jaussoin@belledonne-communications.com>
- Import and configure the new API package
* Thu Jul 4 2019 Sylvain Berfini <sylvain.berfini@belledonne-communications.com>
- New files layout
* Fri Jun 28 2019 Johan Pascal <johan.pascal@belledonne-communications.com>
-
* Fri May 18 2018 Matthieu TANON <matthieu.tanon@belledonne-communications.com>
- Initial RPM release.

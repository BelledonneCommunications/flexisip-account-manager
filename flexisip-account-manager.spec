# -*- rpm-spec -*-
#%define _prefix    @CMAKE_INSTALL_PREFIX@
#%define pkg_prefix @BC_PACKAGE_NAME_PREFIX@

# re-define some directories for older RPMBuild versions which don't. This messes up the doc/ dir
# taken from https://fedoraproject.org/wiki/Packaging:RPMMacros?rd=Packaging/RPMMacros
#%define _datarootdir       %{_prefix}/share
#%define _datadir           %{_datarootdir}
#%define _docdir            %{_datadir}/doc

%define build_number 17
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
Source0:	flexisip-account-manager.tar.gz
#BuildRoot:      %{_tmppath}/%{name}-%{version}-%{release}-buildroot

# dependencies
Requires:	rh-php73-php rh-php73-php-xmlrpc rh-php73-php-pdo rh-php73-php-mysqlnd rh-php73-php-mbstring

%description
PHP server for Linphone and Flexisip providing module for account creation.


%prep
%setup -n flexisip-account-manager

%install
rm -rf "$RPM_BUILD_ROOT"
mkdir -p "$RPM_BUILD_ROOT/opt/belledonne-communications/share/flexisip-account-manager"
cp -R src/* "$RPM_BUILD_ROOT/opt/belledonne-communications/share/flexisip-account-manager/"
mkdir -p "$RPM_BUILD_ROOT/opt/belledonne-communications/share/flexisip-account-manager/flexiapi"
cp -R flexiapi/* "$RPM_BUILD_ROOT/opt/belledonne-communications/share/flexisip-account-manager/flexiapi"
cp README* "$RPM_BUILD_ROOT/opt/belledonne-communications/share/flexisip-account-manager/"
mkdir -p "$RPM_BUILD_ROOT/etc/flexisip-account-manager"
cp -R conf/* "$RPM_BUILD_ROOT/etc/flexisip-account-manager/"
mkdir -p $RPM_BUILD_ROOT/opt/rh/httpd24/root/etc/httpd/conf.d
cp httpd/flexisip-account-manager.conf "$RPM_BUILD_ROOT/opt/rh/httpd24/root/etc/httpd/conf.d/"


%post
if [ $1 -eq 1 ] ; then
mkdir -p /var/opt/belledonne-communications/log
touch /var/opt/belledonne-communications/log/account-manager.log
chown apache:apache /var/opt/belledonne-communications/log/account-manager.log
chcon -t httpd_sys_rw_content_t /var/opt/belledonne-communications/log/account-manager.log
setsebool -P httpd_can_network_connect_db on
fi


%files
/opt/belledonne-communications/share/flexisip-account-manager/flexiapi/**/*
/opt/belledonne-communications/share/flexisip-account-manager/api/account/*.php
/opt/belledonne-communications/share/flexisip-account-manager/config/*.php
/opt/belledonne-communications/share/flexisip-account-manager/database/*.php
/opt/belledonne-communications/share/flexisip-account-manager/misc/*.php
/opt/belledonne-communications/share/flexisip-account-manager/objects/*.php
/opt/belledonne-communications/share/flexisip-account-manager/tools/*.php
/opt/belledonne-communications/share/flexisip-account-manager/xmlrpc/*.php
/opt/belledonne-communications/share/flexisip-account-manager/README*

%config(noreplace) /etc/flexisip-account-manager/*.conf
%config(noreplace) /opt/rh/httpd24/root/etc/httpd/conf.d/flexisip-account-manager.conf

%clean
rm -rf $RPM_BUILD_ROOT

%changelog
* Thu Jul 4 2019 Sylvain Berfini <sylvain.berfini@belledonne-communications.com>
- New files layout
* Fri Jun 28 2019 Johan Pascal <johan.pascal@belledonne-communications.com>
-
* Fri May 18 2018 Matthieu TANON <matthieu.tanon@belledonne-communications.com>
- Initial RPM release.

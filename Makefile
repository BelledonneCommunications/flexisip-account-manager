$(eval GIT_DESCRIBE = $(shell sh -c "git describe"))
OUTPUT_DIR = ${CURDIR}
prepare:
	cd flexiapi && composer install --no-dev

prepare-dev:
	cd flexiapi && composer install

package-common:
	rm -rf $(OUTPUT_DIR)/flexisip-account-manager
	mkdir $(OUTPUT_DIR)/flexisip-account-manager
	mkdir -p $(OUTPUT_DIR)/rpmbuild/SPECS
	mkdir -p $(OUTPUT_DIR)/rpmbuild/SOURCES

	# XMLRPC
	cp -R --parents src/**/*.php $(OUTPUT_DIR)/flexisip-account-manager/
	cp -R --parents src/api/**/*.php $(OUTPUT_DIR)/flexisip-account-manager/
	cp -R --parents conf/*.conf $(OUTPUT_DIR)/flexisip-account-manager/

	# FlexiAPI
	cp -R --parents flexiapi/**/* $(OUTPUT_DIR)/flexisip-account-manager/
	cp flexiapi/composer* $(OUTPUT_DIR)/flexisip-account-manager/flexiapi/
	cp flexiapi/README.md $(OUTPUT_DIR)/flexisip-account-manager/flexiapi/
	cp flexiapi/.env.example $(OUTPUT_DIR)/flexisip-account-manager/flexiapi/.env.example
	cp flexiapi/artisan $(OUTPUT_DIR)/flexisip-account-manager/flexiapi/
	cp flexiapi/phpunit.xml $(OUTPUT_DIR)/flexisip-account-manager/flexiapi/

	# General
	cp README.md $(OUTPUT_DIR)/flexisip-account-manager/
	cp -R httpd/ $(OUTPUT_DIR)/flexisip-account-manager/
	cp flexisip-account-manager.spec $(OUTPUT_DIR)/rpmbuild/SPECS/

	tar cvf flexisip-account-manager.tar.gz -C $(OUTPUT_DIR) flexisip-account-manager
	mv flexisip-account-manager.tar.gz $(OUTPUT_DIR)/rpmbuild/SOURCES/flexisip-account-manager.tar.gz

package-end-common:
	rm -rf $(OUTPUT_DIR)/flexisip-account-manager
	rm -rf $(OUTPUT_DIR)/rpmbuild/SPECS $(OUTPUT_DIR)/rpmbuild/SOURCES $(OUTPUT_DIR)/rpmbuild/SRPMS $(OUTPUT_DIR)/rpmbuild/BUILD $(OUTPUT_DIR)/rpmbuild/BUILDROOT

rpm-only:
	rpmbuild -v -bb --define '_topdir $(OUTPUT_DIR)/rpmbuild' --define "_rpmdir $(OUTPUT_DIR)/rpmbuild" $(OUTPUT_DIR)/rpmbuild/SPECS/flexisip-account-manager.spec
	@echo "== RPM Package Created =="

deb-only:
	rpmbuild -v -bb --with deb --define '_topdir $(OUTPUT_DIR)/rpmbuild' --define "_rpmfilename tmp.rpm" --define "_rpmdir $(OUTPUT_DIR)/rpmbuild" $(OUTPUT_DIR)/rpmbuild/SPECS/flexisip-account-manager.spec
	fakeroot alien -g --scripts $(OUTPUT_DIR)/rpmbuild/tmp.rpm
	rm -r $(OUTPUT_DIR)/rpmbuild
	rm -rf $(OUTPUT_DIR)/*.orig
	sed -i 's/Depends:.*/Depends: $${shlibs:Depends}, php, php-xmlrpc, php-pdo, php-gd, php-mysqlnd, php-mbstring, php-sqlite3/g' $(OUTPUT_DIR)/bc-flexisip-account-manager*/debian/control
	cd `ls -rt $(OUTPUT_DIR) | tail -1` && dpkg-buildpackage --no-sign
	@echo "== DEB Package Created =="

	# Cleanup
	ls -d */ | cut -f1 -d'/' | grep bc-flexisip-account-manager | xargs rm -rf
	ls bc-flexisip-account-manager* | grep -v deb | xargs rm

rpm: prepare package-common rpm-only package-end-common
rpm-dev: prepare-dev package-common rpm-only package-end-common
deb: prepare package-common deb-only package-end-common
deb-dev: prepare-dev package-common deb-only package-end-common

.PHONY: rpm

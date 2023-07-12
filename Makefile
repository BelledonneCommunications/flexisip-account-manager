OUTPUT_DIR = ${CURDIR}
GIT_DESCRIBE = $(shell sh -c "git describe --long" 2>/dev/null)

DESCRIBE_PARTS = $(subst -, ,$(GIT_DESCRIBE))
VERSION_TAG = $(word 1,$(DESCRIBE_PARTS))
STATUS_TAG = $(word 2,$(DESCRIBE_PARTS))
STATUS_DISTANCE_TAG = $(word 3,$(DESCRIBE_PARTS))
COMMIT_HASH_TAG = $(word 4,$(DESCRIBE_PARTS))
CLEAN_COMMIT_HASH_TAG = $(COMMIT_HASH_TAG:g%=%)

package-semvers:
	mkdir -p build
	cp flexisip-account-manager.spec flexisip-account-manager.spec.run
	sed -i 's/MAKE_FILE_VERSION_SEARCH/$(VERSION_TAG)/g' $(CURDIR)/flexisip-account-manager.spec.run

ifneq (,$(filter alpha beta,$(STATUS_TAG)))
	sed -i 's/MAKE_FILE_BUILD_NUMBER_SEARCH/0.$(STATUS_TAG).$(STATUS_DISTANCE_TAG)+$(CLEAN_COMMIT_HASH_TAG)/g' $(CURDIR)/flexisip-account-manager.spec.run
else
	sed -i 's/MAKE_FILE_BUILD_NUMBER_SEARCH/1/g' $(CURDIR)/flexisip-account-manager.spec.run
endif

cleanup-package-semvers:
	rm flexisip-account-manager.spec.run

prepare:
	cd flexiapi && php composer.phar install --ignore-platform-req=ext-redis --no-dev

prepare-dev:
	cd flexiapi && php composer.phar install --ignore-platform-req=ext-redis

package-common:
	rm -rf $(OUTPUT_DIR)/flexisip-account-manager
	mkdir $(OUTPUT_DIR)/flexisip-account-manager
	mkdir -p $(OUTPUT_DIR)/rpmbuild/SPECS
	mkdir -p $(OUTPUT_DIR)/rpmbuild/SOURCES

	# FlexiAPI
	cp -R --parents flexiapi/**/* $(OUTPUT_DIR)/flexisip-account-manager/
	cp flexiapi/composer* $(OUTPUT_DIR)/flexisip-account-manager/flexiapi/
	cp README.md $(OUTPUT_DIR)/flexisip-account-manager/
	cp flexiapi/.env.example $(OUTPUT_DIR)/flexisip-account-manager/flexiapi/.env.example
	cp flexiapi/artisan $(OUTPUT_DIR)/flexisip-account-manager/flexiapi/
	cp flexiapi/phpunit.xml $(OUTPUT_DIR)/flexisip-account-manager/flexiapi/
	cp flexiapi/phpcs.xml $(OUTPUT_DIR)/flexisip-account-manager/flexiapi/
	cp flexiapi/phpmd.xml $(OUTPUT_DIR)/flexisip-account-manager/flexiapi/

	# General
	cp -R httpd/ $(OUTPUT_DIR)/flexisip-account-manager/
	cp -R cron/ $(OUTPUT_DIR)/flexisip-account-manager/
	cp flexisip-account-manager.spec.run $(OUTPUT_DIR)/rpmbuild/SPECS/flexisip-account-manager.spec

	tar cvf flexisip-account-manager.tar.gz -C $(OUTPUT_DIR) flexisip-account-manager
	mv flexisip-account-manager.tar.gz $(OUTPUT_DIR)/rpmbuild/SOURCES/flexisip-account-manager.tar.gz

package-end-common:
	rm -rf $(OUTPUT_DIR)/flexisip-account-manager
	rm -rf $(OUTPUT_DIR)/rpmbuild/SPECS $(OUTPUT_DIR)/rpmbuild/SOURCES $(OUTPUT_DIR)/rpmbuild/SRPMS $(OUTPUT_DIR)/rpmbuild/BUILD $(OUTPUT_DIR)/rpmbuild/BUILDROOT

rpm-only:
	rpmbuild -v -bb --define 'dist .el8' --define '_topdir $(OUTPUT_DIR)/rpmbuild' --define "_rpmdir $(OUTPUT_DIR)/rpmbuild" $(OUTPUT_DIR)/rpmbuild/SPECS/flexisip-account-manager.spec
	@echo "📦✅ RPM Package Created"

	@echo "🧹 Cleanup"
	mv rpmbuild/*/*.rpm build/.
	rm -r rpmbuild

deb-only:
	rpmbuild -v -bb --with deb --define '_topdir $(OUTPUT_DIR)/rpmbuild' --define "_rpmfilename tmp.rpm" --define "_rpmdir $(OUTPUT_DIR)/rpmbuild" $(OUTPUT_DIR)/rpmbuild/SPECS/flexisip-account-manager.spec
	fakeroot alien -g -k --scripts $(OUTPUT_DIR)/rpmbuild/tmp.rpm
	rm -r $(OUTPUT_DIR)/rpmbuild
	rm -rf $(OUTPUT_DIR)/*.orig
	sed -i 's/Depends:.*/Depends: $${shlibs:Depends}, php (>= 8.0), php-xml, php-pdo, php-gd, php-redis, php-mysql, php-mbstring, php-sqlite3/g' $(OUTPUT_DIR)/bc-flexisip-account-manager*/debian/control

	cd `ls -rt $(OUTPUT_DIR) | tail -1` && dpkg-buildpackage --no-sign
	@echo "📦✅ DEB Package Created"

	@echo "🧹 Cleanup"
	ls -d */ | cut -f1 -d'/' | grep bc-flexisip-account-manager | xargs rm -rf
	ls bc-flexisip-account-manager* | grep -v "\.deb" | xargs rm

	mv *.deb build/.

rpm: prepare package-semvers package-common rpm-only cleanup-package-semvers package-end-common
rpm-dev: prepare-dev package-semvers package-common rpm-only cleanup-package-semvers package-end-common
deb: prepare package-semvers package-common deb-only cleanup-package-semvers package-end-common
deb-dev: prepare-dev package-semvers package-common deb-only cleanup-package-semvers package-end-common

.PHONY: rpm

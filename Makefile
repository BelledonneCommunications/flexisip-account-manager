$(eval GIT_DESCRIBE = $(shell sh -c "git describe"))
OUTPUT_DIR = ${CURDIR}
prepare:
	cd flexiapi && composer install --no-dev
		
rpm-only: 
	rm -rf $(OUTPUT_DIR)/flexisip-account-manager
	mkdir $(OUTPUT_DIR)/flexisip-account-manager
	mkdir -p $(OUTPUT_DIR)/rpmbuild/SPECS
	mkdir -p $(OUTPUT_DIR)/rpmbuild/SOURCES
	cp -R --parents src/**/*.php $(OUTPUT_DIR)/flexisip-account-manager/
	cp -R --parents src/api/**/*.php $(OUTPUT_DIR)/flexisip-account-manager/
	cp -R --parents conf/*.conf $(OUTPUT_DIR)/flexisip-account-manager/
	cp -R --parents flexiapi/**/* $(OUTPUT_DIR)/flexisip-account-manager/
	cp README.md $(OUTPUT_DIR)/flexisip-account-manager/
	cp -R httpd/ $(OUTPUT_DIR)/flexisip-account-manager/
	cp flexisip-account-manager.spec $(OUTPUT_DIR)/rpmbuild/SPECS/
	tar cvf flexisip-account-manager.tar.gz -C $(OUTPUT_DIR) flexisip-account-manager
	mv flexisip-account-manager.tar.gz $(OUTPUT_DIR)/rpmbuild/SOURCES/flexisip-account-manager.tar.gz
	rpmbuild -v -bb  --define '_topdir $(OUTPUT_DIR)/rpmbuild' --define "_rpmdir $(OUTPUT_DIR)/rpmbuild" $(OUTPUT_DIR)/rpmbuild/SPECS/flexisip-account-manager.spec
	rm -rf $(OUTPUT_DIR)/flexisip-account-manager

rpm: prepare rpm-only

.PHONY: rpm

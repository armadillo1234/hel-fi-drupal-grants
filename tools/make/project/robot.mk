PHONY += test-robot
test-robot: ## Run Robot framework tests in docker container (all tests)
	docker run \
			-v $(PWD)/test/logs:/opt/robotframework/reports:Z \
			-v $(PWD)/test:/opt/robotframework/tests:Z \
      -e ROBOT_OPTIONS="--variable environment:local --variable browser:chrome ${ROBOT_OPTIONS}" \
			--add-host $(DRUPAL_HOSTNAME):127.0.0.1 \
			--net="host" \
			-it \
			ppodgorsek/robot-framework:5.0.0

PHONY += test-robot-public
test-robot-public: ## Run Robot framework tests in docker container (Public)
	docker run \
			-v $(PWD)/test/logs:/opt/robotframework/reports:Z \
			-v $(PWD)/test:/opt/robotframework/tests:Z \
      -e ROBOT_OPTIONS="--variable environment:local --variable browser:chrome --suite public ${ROBOT_OPTIONS}" \
			--add-host $(DRUPAL_HOSTNAME):127.0.0.1 \
			--net="host" \
			-it \
			ppodgorsek/robot-framework:5.0.0

PHONY += test-robot-profile
test-robot-profile: ## Run Robot framework tests in docker container (Profile)
	docker run \
			-v $(PWD)/test/logs:/opt/robotframework/reports:Z \
			-v $(PWD)/test:/opt/robotframework/tests:Z \
      -e ROBOT_OPTIONS="--variable environment:local --variable browser:chrome --suite profile ${ROBOT_OPTIONS}" \
			--add-host $(DRUPAL_HOSTNAME):127.0.0.1 \
			--net="host" \
			-it \
			ppodgorsek/robot-framework:5.0.0

PHONY += test-robot-oma-asiointi
test-robot-oma-asiointi: ## Run Robot framework tests in docker container (Oma asiointi)
	docker run \
			-v $(PWD)/test/logs:/opt/robotframework/reports:Z \
			-v $(PWD)/test:/opt/robotframework/tests:Z \
      -e ROBOT_OPTIONS="--variable environment:local --variable browser:chrome --suite oma_asiointi ${ROBOT_OPTIONS}" \
			--add-host $(DRUPAL_HOSTNAME):127.0.0.1 \
			--net="host" \
			-it \
			ppodgorsek/robot-framework:5.0.0

PHONY += test-robot-form-kasvatus
test-robot-form-kasvatus: ## Run Robot framework tests in docker container (Kasvatus ja koulutus yleisavustushakemus)
	docker run \
			-v $(PWD)/test/logs:/opt/robotframework/reports:Z \
			-v $(PWD)/test:/opt/robotframework/tests:Z \
      -e ROBOT_OPTIONS="--variable environment:local --variable browser:chrome --suite form_kasvatus_ja_koulutus_yleisavustu ${ROBOT_OPTIONS}" \
			--add-host $(DRUPAL_HOSTNAME):127.0.0.1 \
			--net="host" \
			-it \
			ppodgorsek/robot-framework:5.0.0

PHONY += test-robot-form-yleis
test-robot-form-yleis: ## Run Robot framework tests in docker container (Yleisavustushakemus)
	docker run \
			-v $(PWD)/test/logs:/opt/robotframework/reports:Z \
			-v $(PWD)/test:/opt/robotframework/tests:Z \
      -e ROBOT_OPTIONS="--variable environment:local --variable browser:chrome --suite form_yleisavustushakemus ${ROBOT_OPTIONS}" \
			--add-host $(DRUPAL_HOSTNAME):127.0.0.1 \
			--net="host" \
			-it \
			ppodgorsek/robot-framework:5.0.0

# Docker CLI container
CLI_SERVICE=app
CLI_USER=druid
CLI_SHELL=bash


PHONY += drush-sync-db
drush-sync-db: ## Sync database
	$(call drush,sql-drop --quiet -y)
ifeq ($(DUMP_SQL_EXISTS),yes)
	$(call step,Import local SQL dump... OVERRRIDE!!!!!!)
	$(call drush,sql-query --file=${DOCKER_PROJECT_ROOT}/$(DUMP_SQL_FILENAME) && echo 'SQL dump imported')
else
	$(call step,Sync database from @$(DRUPAL_SYNC_SOURCE)... OVERRIDE!!!!!)
	$(call drush,sql-sync -y --structure-tables-key=common,key_value_expire @$(DRUPAL_SYNC_SOURCE) @self)
endif
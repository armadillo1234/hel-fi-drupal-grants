DRUPAL_POST_INSTALL_TARGETS += drush-gwi

PHONY += drush-gwi
drush-gwi: ## Export configuration
	$(call step,Import forms...\n)
	$(call drush,gwi -y)
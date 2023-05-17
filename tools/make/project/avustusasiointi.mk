DRUPAL_POST_INSTALL_TARGETS += drush-gwi
AVU_DRUPAL_FRESH_TARGETS := up build sync post-install

PHONY += drush-gwi
drush-gwi: ## Export configuration
	$(call step,Import forms...\n)
	$(call drush,gwi -y)

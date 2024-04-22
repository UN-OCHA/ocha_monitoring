# OCHA Monitoring

Provides custom sensors and enables only those that we need.

## Enforced monitors

You can alter the enforced monitors using a hook
`hook_ocha_monitoring_active_monitors_alter`, see [API](./ocha_monitoring.api.module)

Current enforced monitors:

- core_cron_last_run_age
- monitoring_installed_modules
- update_contrib
- update_core
- ocha_current_drupal_version
- ocha_current_php_version
- ocha_current_release
- ocha_deployment_identifier
- ocha_gtm_barebones
- ocha_un_date
- ocha_common_design
- ocha_env_link_fixer
- ocha_ocha_monitoring
- ocha_current_composer_version

## Installation

`composer require unocha/ocha_monitoring`

`drush pm:install ocha_monitoring`

`drush config:export`

Commit the monitoring-related config changes (including composer files and
`config/core.extension.yml`).

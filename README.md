# OCHA Monitoring

Provides custom sensors and enables only those that we need.

## Installation

`composer require unocha/ocha_monitoring`

`drush pm:install ocha_monitoring`

`drush config:export`

Commit the following files:
`composer.json`
`composer.lock`
`config/core.extension.yml`
`config/views.view.monitoring_sensor_results.yml`
`config/language/*/views.view.monitoring_sensor_results.yml`
`config/monitoring.settings.yml`
`config/monitoring.sensor_config.ocha_*`

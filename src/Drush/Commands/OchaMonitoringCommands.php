<?php

namespace Drupal\ocha_monitoring\Drush\Commands;

use Consolidation\AnnotatedCommand\Hooks\HookManager;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 */
final class OchaMonitoringCommands extends DrushCommands {

  /**
   * Enforce mandatory sensors for all sites.
   */
  #[CLI\Hook(type: HookManager::POST_COMMAND_HOOK, target: 'deploy:hook')]
  public function enforceMandatorySensors() : void {
    $this->logger()->success(dt('Enforcing mandatory sensors.'));
    ocha_monitoring_enforce_monitors();
  }

}

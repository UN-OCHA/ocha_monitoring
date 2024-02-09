<?php

namespace Drupal\ocha_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\monitoring\Entity\SensorConfig;
use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Monitors GTM.
 *
 * @SensorPlugin(
 *   id = "ocha_env_link_fixer",
 *   label = @Translation("Environment Link Fixer"),
 *   description = @Translation("Monitors Environment Link Fixer."),
 *   addable = FALSE
 * )
 */
class OchaEnvironmentLinkFixerSensorPlugin extends SensorPluginBase {

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public function __construct(SensorConfig $sensor_config, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler) {
    parent::__construct($sensor_config, $plugin_id, $plugin_definition);
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, SensorConfig $sensor_config, $plugin_id, $plugin_definition) {
    return new static(
      $sensor_config,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function runSensor(SensorResultInterface $result) {
    $module_name = 'env_link_fixer';

    if (!$this->moduleHandler->moduleExists($module_name)) {
      $result->setValue('Module not installed');
      $result->setMessage('Module not installed');
      $result->setStatus(SensorResultInterface::STATUS_UNKNOWN);
      return;
    }

    $available = update_get_available();
    if (!isset($available[$module_name])) {
      $result->setValue('Module not found in updates');
      $result->setMessage('Module not found in updates');
      $result->setStatus(SensorResultInterface::STATUS_UNKNOWN);
      return;
    }

    $project_data = update_calculate_project_data($available);
    if (!isset($project_data[$module_name])) {
      $result->setValue('Module not found in updates');
      $result->setMessage('Module not found in updates');
      $result->setStatus(SensorResultInterface::STATUS_UNKNOWN);
      return;
    }

    if (isset($project_data[$module_name]['latest_version']) && isset($project_data[$module_name]['existing_version'])) {
      if ($project_data[$module_name]['latest_version'] == $project_data[$module_name]['existing_version']) {
        $result->setValue('Latest version install');
        $result->setMessage('Latest version install');
        $result->setStatus(SensorResultInterface::STATUS_OK);
        return;
      }

      $result->setValue('Environment Link Fixer not up to date');
      $result->setMessage($project_data[$module_name]['existing_version'] . ' installed, ' . $project_data[$module_name]['latest_version'] . ' available');
      $result->setStatus(SensorResultInterface::STATUS_WARNING);
    }

    $result->setValue('Environment Link Fixer not up to date');
    $result->setMessage('Environment Link Fixer not up to date');
    $result->setStatus(SensorResultInterface::STATUS_WARNING);
  }

}

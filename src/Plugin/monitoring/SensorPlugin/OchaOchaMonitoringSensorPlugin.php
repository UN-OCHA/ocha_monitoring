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
 *   id = "ocha_ocha_monitoring",
 *   label = @Translation("OCHA Monitoring"),
 *   description = @Translation("Monitors OCHA Monitoring."),
 *   addable = FALSE
 * )
 */
class OchaOchaMonitoringSensorPlugin extends SensorPluginBase {

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
    if (!$this->moduleHandler->moduleExists('ocha_monitoring')) {
      $result->setValue('Module not installed');
      $result->setMessage('Module not installed');
      $result->setStatus(SensorResultInterface::STATUS_UNKNOWN);
      return;
    }

    $version_info = ocha_monitoring_get_version_info_from_composer('unocha', 'ocha_monitoring');
    if (empty($version_info)) {
      $result->setValue('Module not found in composer');
      $result->setMessage('Module not found in composer');
      $result->setStatus(SensorResultInterface::STATUS_UNKNOWN);
      return;
    }

    if ($version_info['version'] == $version_info['latest']) {
      $result->setValue('Latest version install');
      $result->setMessage('Latest version install');
      $result->setStatus(SensorResultInterface::STATUS_OK);
      return;
    }

    $result->setValue('OCHA Monitoring not up to date');
    $result->setMessage($version_info['version'] . ' installed, ' . $version_info['latest'] . ' available');
    $result->setStatus(SensorResultInterface::STATUS_WARNING);
  }

}

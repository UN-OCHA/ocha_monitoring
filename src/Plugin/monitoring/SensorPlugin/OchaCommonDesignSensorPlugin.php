<?php

namespace Drupal\ocha_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\monitoring\Entity\SensorConfig;
use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Monitors GTM.
 *
 * @SensorPlugin(
 *   id = "ocha_common_design",
 *   label = @Translation("Common design"),
 *   description = @Translation("Monitors Common design."),
 *   addable = FALSE
 * )
 */
class OchaCommonDesignSensorPlugin extends SensorPluginBase {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * {@inheritdoc}
   */
  public function __construct(SensorConfig $sensor_config, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ThemeHandlerInterface $theme_handler) {
    parent::__construct($sensor_config, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->themeHandler = $theme_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, SensorConfig $sensor_config, $plugin_id, $plugin_definition) {
    return new static(
      $sensor_config,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('theme_handler'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function runSensor(SensorResultInterface $result) {
    if (!$this->themeHandler->themeExists('common_design')) {
      $result->setValue('CD not installed');
      $result->setMessage('CD not installed');
      $result->setStatus(SensorResultInterface::STATUS_CRITICAL);
    }

    $default = $this->themeHandler->getDefault();
    $theme = $this->themeHandler->getTheme($default);

    if (!isset($theme->base_themes['common_design'])) {
      $result->setValue('Default theme is not using CD');
      $result->setMessage('Default theme is not using CD');
      $result->setStatus(SensorResultInterface::STATUS_WARNING);
      return;
    }

    $version_info = ocha_monitoring_get_version_info_from_composer('unocha', 'common_design');
    if ($version_info['version'] == $version_info['latest']) {
      $result->setValue('Latest version install');
      $result->setMessage('Latest version install');
      $result->setStatus(SensorResultInterface::STATUS_OK);
      return;
    }

    $result->setValue('CD not up to date');
    $result->setMessage($version_info['version'] . ' installed, ' . $version_info['latest'] . ' available');
    $result->setStatus(SensorResultInterface::STATUS_WARNING);
  }

}

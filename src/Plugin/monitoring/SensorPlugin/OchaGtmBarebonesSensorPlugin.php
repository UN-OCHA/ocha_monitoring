<?php

namespace Drupal\ocha_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\monitoring\Entity\SensorConfig;
use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Monitors GTM.
 *
 * @SensorPlugin(
 *   id = "ocha_gtm_barebones",
 *   label = @Translation("GTM Barebones"),
 *   description = @Translation("Monitors GTM Barebones."),
 *   addable = FALSE
 * )
 */
class OchaGtmBarebonesSensorPlugin extends SensorPluginBase {

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(SensorConfig $sensor_config, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler, ConfigFactoryInterface $config_factory) {
    parent::__construct($sensor_config, $plugin_id, $plugin_definition);
    $this->moduleHandler = $module_handler;
    $this->configFactory = $config_factory;
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
      $container->get('config.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function runSensor(SensorResultInterface $result) {
    if (!$this->moduleHandler->moduleExists('gtm_barebones')) {
      $result->setValue('Module not installed');
      $result->setMessage('Module not installed');
      $result->setStatus(SensorResultInterface::STATUS_CRITICAL);
    }

    $config = $this->configFactory->get('gtm_barebones.settings');
    $tag = $config->get('container_id');
    if (empty($tag)) {
      $result->setValue($tag);
      $result->setMessage('Tag not set');
      $result->setStatus(SensorResultInterface::STATUS_WARNING);
      return;
    }

    if ($tag == 'GTM-xxxxxx') {
      $result->setValue($tag);
      $result->setMessage('Tag not set in Ansible');
      $result->setStatus(SensorResultInterface::STATUS_WARNING);
      return;
    }

    $result->setValue($tag);
    $result->setMessage($tag);
    $result->setStatus(SensorResultInterface::STATUS_OK);
  }

}

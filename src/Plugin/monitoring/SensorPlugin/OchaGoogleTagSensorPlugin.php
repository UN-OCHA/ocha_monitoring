<?php

namespace Drupal\ocha_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\monitoring\Entity\SensorConfig;
use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Monitors GTM.
 *
 * @SensorPlugin(
 *   id = "ocha_google_tag",
 *   label = @Translation("Google tag"),
 *   description = @Translation("Monitors google tag."),
 *   addable = FALSE
 * )
 */
class OchaGoogleTagSensorPlugin extends SensorPluginBase {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public function __construct(SensorConfig $sensor_config, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler) {
    parent::__construct($sensor_config, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('entity_type.manager'),
      $container->get('module_handler'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function runSensor(SensorResultInterface $result) {
    if (!$this->moduleHandler->moduleExists('google_tag')) {
      $result->setValue('Module not installed');
      $result->setMessage('Module not installed');
      $result->setStatus(SensorResultInterface::STATUS_CRITICAL);
      return;
    }

    $storage = $this->entityTypeManager->getStorage('google_tag_container');
    $config_ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('status', 1)
      ->sort('weight')
      ->execute();

    /** @var array<string, \Drupal\google_tag\Entity\TagContainer> $configs */
    $configs = $storage->loadMultiple($config_ids);
    foreach ($configs as $config) {
      $tag = $config->container_id ?? '';
      if ($tag == 'GTM-xxxxxx') {
        $result->setValue($tag);
        $result->setMessage('Tag not set in Ansible');
        $result->setStatus(SensorResultInterface::STATUS_WARNING);
        return;
      }

      $result->setValue($tag);
      $result->setMessage($tag);
      $result->setStatus(SensorResultInterface::STATUS_OK);
      return;
    }

    $result->setValue('No tag found');
    $result->setMessage('No tag found');
    $result->setStatus(SensorResultInterface::STATUS_CRITICAL);
  }

}

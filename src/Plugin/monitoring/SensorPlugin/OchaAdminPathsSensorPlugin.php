<?php

namespace Drupal\ocha_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\block\Entity\Block;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Url;
use Drupal\monitoring\Entity\SensorConfig;
use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Monitors GTM.
 *
 * @SensorPlugin(
 *   id = "ocha_admin_paths",
 *   label = @Translation("Admin paths"),
 *   description = @Translation("Monitors admin paths."),
 *   addable = FALSE
 * )
 */
class OchaAdminPathsSensorPlugin extends SensorPluginBase {

  /**
   * Route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * {@inheritdoc}
   */
  public function __construct(SensorConfig $sensor_config, $plugin_id, $plugin_definition, RouteProviderInterface $route_provider) {
    parent::__construct($sensor_config, $plugin_id, $plugin_definition);
    $this->routeProvider = $route_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, SensorConfig $sensor_config, $plugin_id, $plugin_definition) {
    return new static(
      $sensor_config,
      $plugin_id,
      $plugin_definition,
      $container->get('router.route_provider'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function runSensor(SensorResultInterface $result) {
    $anon = User::getAnonymousUser();
    $routes = $this->routeProvider->getAllRoutes();
    $access_to = [];
    $skipped = [];

    foreach ($routes as $route_name => $route) {
      $path = $route->getPath();
      if (strpos($path, '/admin') === 0) {
        try {
          // Add language by default.
          $vars= [
            'langcode' => 'en',
          ];

          // Check for other parameters, set dummy value.
          $options = $route->getOptions();
          if (isset($options['parameters']) && is_array($options['parameters'])) {
            foreach ($options['parameters'] as $key => $parameter) {
              $vars[$key] = $key;
            }
          }

          // Build URL.
          $url = Url::fromRoute($route_name, $vars);

          // Check access.
          if ($url && $url->access($anon)) {
            $access_to[] = $path;
          }
        }
        catch (\Exception $e) {
          // Skip inaccessible paths.
          $skipped[] = $path;
        }
        catch (\Throwable $e) {
          // Skip inaccessible paths.
          $skipped[] = $path;
        }
      }
    }

    if (empty($access_to)) {
      $result->setValue('No access to admin paths');
      $result->setMessage('No access to admin paths');
      $result->setStatus(SensorResultInterface::STATUS_OK);
      return;
    }

    $result->setValue('Anonymous user has access to admin paths');
    $result->setMessage(implode(', ', $access_to));
    $result->setStatus(SensorResultInterface::STATUS_CRITICAL);
  }

}

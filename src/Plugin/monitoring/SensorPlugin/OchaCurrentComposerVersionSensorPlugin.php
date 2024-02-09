<?php

namespace Drupal\ocha_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;

/**
 * Monitors current Composer version.
 *
 * @SensorPlugin(
 *   id = "ocha_current_composer_version",
 *   label = @Translation("Current Composer version"),
 *   description = @Translation("Current Composer version."),
 *   addable = FALSE
 * )
 *
 * Based on ocha_current_php_version.
 */
class OchaCurrentComposerVersionSensorPlugin extends SensorPluginBase {

  /**
   * Major version.
   */
  private $major = 0;

  /**
   * Minor version.
   */
  private $minor = 0;

  /**
   * Release version.
   */
  private $release = 0;

  /**
   * Release date.
   */
  private $datetime = '';

  /**
   * {@inheritdoc}
   */
  public function runSensor(SensorResultInterface $result) {
    if ($this->getComposerVersion() == TRUE) {
      $result->setValue($this->major . '.' . $this->minor . '.' . $this->release);
      $result->setMessage('Composer ' . $this->major . '.' . $this->minor . '.' . $this->release . ' ' . $this->datetime);
      $result->setStatus(SensorResultInterface::STATUS_INFO);
    }
    else {
      $result->setValue('');
      $result->setMessage('Composer version not detected');
      $result->setStatus(SensorResultInterface::STATUS_CRITICAL);
    }
  }

  /**
   * Run composer -V and parse the output to extract the version.
   *
   * @return boolean.
   */
  private function getComposerVersion() {
    $desc = [
      0 => ["pipe", "r"],
      1 => ["pipe", "w"],
      2 => ["pipe", "w"],
    ];

    // Run composer using proc_open, which is allegedly safest.
    $composer = proc_open(['composer', '-V'], $desc, $pipes);

    // Oy vey!
    if (!is_resource($composer)) {
      return FALSE;
    }

    $composer_version_string = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    // Return false on error, so the plugin can set error status.
    // Composer exit status is 0 on success.
    $ret = proc_close($composer);
    if ($ret !== 0) {
      return FALSE;
    }

    // Extract the version string using sscanf().
    // This will break if the string format changes.
    // "Composer version 2.6.6 2023-12-08 18:32:26"
    $ret = sscanf($composer_version_string, "Composer version %d.%d.%d %s %s");

    // We should have an array with 5 values.
    if (count($ret) !== 5) {
        return FALSE;
    }

    // Set the versions.
    $this->major = $ret[0];
    $this->minor = $ret[1];
    $this->release = $ret[2];

    // And the timestamp.
    $this->datetime = $ret[3] . ' ' . $ret[4];

    return TRUE;
  }

}

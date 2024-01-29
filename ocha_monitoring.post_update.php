<?php

/**
 * @file
 * Monitoring file.
 */

/**
 * Re-add al config.
 */
function ocha_monitoring_hook_post_update_v105(&$sandbox) {
  ocha_monitoring_enforce_monitors();
}

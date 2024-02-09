<?php

/**
 * @file
 * Monitoring file.
 */

/**
 * Re-add al config.
 */
function ocha_monitoring_post_update_v105(&$sandbox) {
  ocha_monitoring_enforce_monitors();
}

/**
 * Re-add al config.
 */
function ocha_monitoring_post_update_v106(&$sandbox) {
  ocha_monitoring_enforce_monitors();
}

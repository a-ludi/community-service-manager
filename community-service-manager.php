<?php
/**
 * Plugin Name: Community Service Manager
 * Description: Coordinate and control multiple community service schedules
 *              with multiple volunteers, statistics and different
 *              notifications.
 * Version:     0.1.1a
 * Author:      Arne Ludwig <arne.ludwig@posteo.de>
 * License:     GPLv3
 * Text Domain: community-service-manager
 * Prefix:      csm
 */

/* Copyright © 2015 Arne Ludwig <arne.ludwig@posteo.de>
 *
 * This file is part of Community Service Manager.
 *
 * Community Service Manager is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * Community Service Manager is distributed in the hope that it will be
 * useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
 * Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with Community Service Manager. If not, see <http://www.gnu.org/licenses/>.
 */

if(! function_exists('csm_prevent_direct_execution')) {
  function csm_prevent_direct_execution() {
    defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
  }
}

define('CSM_PLUGIN_FILE', __FILE__);
include_once plugin_dir_path(__FILE__).'/include/functions-debug.php';
include_once plugin_dir_path(__FILE__).'/vendor/class-active-data.php';
include_once plugin_dir_path(__FILE__).'/vendor/class-simple-html-builder.php';
include_once plugin_dir_path(__FILE__).'/vendor/class-simple-calendar-view.php';
include_once plugin_dir_path(__FILE__).'/vendor/class-simple-date-time.php';
include_once plugin_dir_path(__FILE__).'/vendor/class-simple-time-interval.php';
include_once plugin_dir_path(__FILE__).'/vendor/class-simple-fixtures.php';
include_once plugin_dir_path(__FILE__).'/vendor/functions-array.php';
include_once plugin_dir_path(__FILE__).'/vendor/functions-html.php';
include_once plugin_dir_path(__FILE__).'/vendor/functions-str.php';
include_once plugin_dir_path(__FILE__).'/include/class-csm-journal.php';
include_once plugin_dir_path(__FILE__).'/include/class-csm-journal-entry.php';
include_once plugin_dir_path(__FILE__).'/include/class-csm-abstract-db-manager.php';
include_once plugin_dir_path(__FILE__).'/include/class-csm-db-manager.php';
include_once plugin_dir_path(__FILE__).'/include/class-csm-person.php';
include_once plugin_dir_path(__FILE__).'/include/class-csm-volunteer.php';
include_once plugin_dir_path(__FILE__).'/include/class-csm-coordinator.php';

if(defined('WP_DEBUG') && true === WP_DEBUG)
  include_once plugin_dir_path(__FILE__).'/tests/run-tests.php';
?>

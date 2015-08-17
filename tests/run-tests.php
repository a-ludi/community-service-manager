<?php
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
csm_prevent_direct_execution();

if(version_compare(phpversion(), '5.4.0', '<')) {
  add_action('wp_loaded', function() {
    if(array_get($_REQUEST, 'action') !== 'csm-tests')
      return;

    die("You need PHP version >=5.4.0 to run the test suite.");
  });
} else {
  require_once(plugin_dir_path(CSM_PLUGIN_FILE).'/vendor/simpletest/unit_tester.php');
  require_once(plugin_dir_path(CSM_PLUGIN_FILE).'/vendor/simpletest/mock_objects.php');
  require_once(plugin_dir_path(CSM_PLUGIN_FILE).'/vendor/simpletest/collector.php');
  require_once(plugin_dir_path(CSM_PLUGIN_FILE).'/vendor/simpletest/default_reporter.php');
  require_once(plugin_dir_path(CSM_PLUGIN_FILE).'/vendor/class-simple-fixtures.php');
  require_once(plugin_dir_path(CSM_PLUGIN_FILE).'/tests/include/trait-date-time-assertions.php');
  require_once(plugin_dir_path(CSM_PLUGIN_FILE).'/tests/include/class-csm-unit-test-case.php');
  require_once(plugin_dir_path(CSM_PLUGIN_FILE).'/tests/include/class-csm-test-suite.php');
  require_once(plugin_dir_path(CSM_PLUGIN_FILE).'/tests/include/class-csm-unit-tests.php');
  require_once(plugin_dir_path(CSM_PLUGIN_FILE).'/tests/include/functions.php');

  add_action('wp_loaded', 'csm_run_all_tests');
}
?>
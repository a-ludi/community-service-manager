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

require_once(dirname(__FILE__).'/../community-service-manager.php');
require_once(plugin_dir_path(CSM_PLUGIN_FILE).'/vendor/simple-test/unit_tester.php');
require_once(plugin_dir_path(CSM_PLUGIN_FILE).'/vendor/simple-test/mock_objects.php');
require_once(plugin_dir_path(CSM_PLUGIN_FILE).'/vendor/simple-test/collector.php');
require_once(plugin_dir_path(CSM_PLUGIN_FILE).'/vendor/simple-test/default_reporter.php');

class CSM_AllTests extends TestSuite {
  function __construct() {
    parent::__construct('CSM All Tests');
    $this->addFile('unit/test-journal-entry.php');
  }

  function addFile($path) {
    parent::addFile(plugin_dir_path(CSM_PLUGIN_FILE).'tests/'.$path);
  }
}

add_action('wp_loaded', function() {
  if(array_get($_REQUEST, 'action') !== 'csm-tests')
    return;

  $suite = new CSM_AllTests();
  $reporter = new SelectiveReporter(
    SimpleTest::preferred('TextReporter'),
    @$_GET['c'],
    @$_GET['t']
  );
  $suite->run(new SimpleReporterDecorator($reporter));
  die();
});
?>

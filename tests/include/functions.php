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

  function csm_run_all_tests() {
    if(array_get($_REQUEST, 'action') !== 'csm-tests')
      return;

    csm_adjust_wpdb_debug_output();
    $suites = array(
      new CSM_UnitTests()
    );
    foreach($suites as $suite)
      $suite->run(csm_get_decorator());
    die();
  }

  function csm_adjust_wpdb_debug_output() {
    global $wpdb;
    $wpdb->set_output(isset($_GET['f']) ? $_GET['f'] : 'html');
  }

  function csm_get_decorator() {
    static $decorator = null;
    if(is_null($decorator))
      $decorator = new SimpleReporterDecorator(csm_get_reporter());

    return $decorator;
  }

  function csm_get_reporter() {
    static $reporter = null;
    static $formats = array(
      'html' => 'HTMLReporter',
      'text' => 'TextReporter'
    );

    if(is_null($reporter)) {
      $case = isset($_GET['c']) ? $_GET['c'] : null;
      $test = isset($_GET['t']) ? $_GET['t'] : null;
      $format = isset($_GET['f']) && isset($formats[strtolower($_GET['f'])]) ?
        $formats[strtolower($_GET['f'])] :
        $formats['html'];
      $format = SimpleTest::preferred($format);

      $reporter = new SelectiveReporter($format, $case, $test);
    }

    return $reporter;
  }
?>
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
    csm_adjust_php_error_output();
    $suites = array(
      new CSM_UnitTests()
    );

    csm_run_suites($suites);
    exit();
  }

  function csm_run_suites($suites) {
    $success = true;
    foreach($suites as $suite)
      $success = $success && $suite->run(csm_get_decorator());
    return $success;
  }

  function csm_adjust_php_error_output() {
    ini_set('html_errors', 'html' == csm_get_format());
  }

  function csm_adjust_wpdb_debug_output() {
    global $wpdb;
    $wpdb->format = csm_get_format();
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

    if(is_null($reporter))
      $reporter = new SelectiveReporter(
        SimpleTest::preferred($formats[csm_get_format()]),
        csm_get_case(),
        csm_get_test()
      );

    return $reporter;
  }

  function csm_get_case() {
    return array_get($_GET, 'c');
  }

  function csm_get_test() {
    return array_get($_GET, 't');
  }

  function csm_get_format() {
    return strtolower(array_get($_GET, 'f', 'html'));
  }
?>
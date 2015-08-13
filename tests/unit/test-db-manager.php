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

// TODO needs to be a partial mock...
Mock::generatePartial(
  'CSM_AbstractDBManager',
  'CSM_MockDBManager',
  array(
    // additional methods in subclass
    'do_purge_db',
    'migrate_first_migration',
    'migrate_second_migration',
    'migrate_third_migration',
    'migrate_fourth_migration'
  )
);
Mock::generate('wpdb', 'mock_wpdb');
class TestDBManager extends CSM_UnitTestCase {
  function __construct() {
    parent::__construct(array());
    global $wpdb;
    $this->orig_wpdb = $wpdb;
    $this->wpdb = $wpdb;
  }

  function use_wpdb($kind) {
    global $wpdb;
    switch($kind) {
      case 'original':
        $wpdb = $this->orig_wpdb;
        break;
      default:
      case 'mock':
        $wpdb = $this->mock_wpdb;
        break;
    }
    $this->wpdb = $wpdb;
  }

  function setUp() {
    parent::setUp();
    $this->db_manager = new CSM_MockDBManager();
    $this->db_manager->returns('migrate_first_migration', true);
    $this->db_manager->returns('migrate_second_migration', true);
    $this->db_manager->returns('migrate_third_migration', true);
    $this->db_manager->returns('migrate_fourth_migration', true);
    $this->mock_wpdb = new mock_wpdb();
    $this->use_wpdb('mock');
  }

  function expectSetDBVersion($version='*', $msg=null) {
    if(is_null($msg))
      $msg = "Expected sets db version to [$version]";

    $this->wpdb->expectOnce(
      'query',
      array(
        new PatternExpectation("/csm_db_version[[:blank:]]*=[[:blank:]]*$version/")
      ),
      $msg
    );
  }

  function test_db_migrator_exists() {
    $this->assertTrue(
      class_exists('CSM_AbstractDBManager'),
      'Class CSM_AbstractDBManager should exist'
    );
    $this->assertTrue(
      class_exists('CSM_DBManager'),
      'Class CSM_DBManager should exist'
    );
  }

  function test_db_manager_exists() {
    $this->assertTrue(
      class_exists('CSM_AbstractDBManager'),
      'Class CSM_AbstractDBManager should exist'
    );
    $this->assertTrue(
      class_exists('CSM_DBManager'),
      'Class CSM_DBManager should exist'
    );
  }

  function test_db_version() {
    $this->assertIsA($this->db_manager->db_version(), 'int');
  }

  function test_migrate_calls_migrations_in_correct_order() {
    $this->db_manager->expectAt(0, 'migrate_first_migration', array());
    $this->db_manager->expectAt(1, 'migrate_second_migration', array());
    $this->db_manager->expectAt(2, 'migrate_third_migration', array());
    $this->db_manager->expectAt(3, 'migrate_fourth_migration', array());
    $this->db_manager->migrate();
  }

  function test_migrate_updates_db_version() {
    $this->expectSetDBVersion(3);
    $this->db_manager->migrate();
  }

  function test_migrate_stops_on_failure() {
    $this->db_manager->expectOnce('migrate_first_migration');
    $this->db_manager->expectOnce('migrate_second_migration');
    $this->db_manager->returns('migrate_second_migration', false);
    $this->db_manager->expectNever('migrate_third_migration');
    $this->db_manager->expectNever('migrate_fourth_migration');
    $this->db_manager->migrate();
  }

  function test_migrate_updates_db_version_on_failure() {
    $this->db_manager->returns('migrate_third_migration', false);
    $this->expectSetDBVersion(2);
    $this->db_manager->migrate();
  }

  function test_purge_db_calls_inferiors_method() {
    $this->db_manager->expectOnce('do_purge_db');
    $this->db_manager->purge_db();
  }

  function test_purge_db_resets_db_version() {
    $this->db_manager->purge_db();
    $this->expectSetDBVersion(0);
  }
}
?>

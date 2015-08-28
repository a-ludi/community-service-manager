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

abstract class CSM_UnitTestCase extends UnitTestCase {
  public static $auto_id_fixtures = array(
    'wp_csm_journal' => true,
    'wp_users' => true,
    'wp_usermeta' => 'umeta_id'
  );
  private static $_dbh;
  private static $_fixtures = null;
  private static $fixture_dir;
  private static $php_env;
  protected $dbh;
  protected $fixtures;
  protected $which_fixtures;

  function __construct($which_fixtures) {
    if(is_null(self::$_fixtures)) {
      self::$_dbh = new PDO(
        'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET,
        DB_USER,
        DB_PASSWORD
      );
      self::$_fixtures = new SimpleYamlFixtures(self::$_dbh);
      self::$fixture_dir = plugin_dir_path(CSM_PLUGIN_FILE).'/tests/fixtures/';
      self::$php_env = array(
        'now' => new SimpleDateTime(),
        'secs_per_min' => 60,
        'secs_per_hour' => 60*60,
        'secs_per_day' => 60*60*24,
        'secs_per_week' => 60*60*24*7,
      );

      $fixture_files = preg_grep('/.*\.yml$/', scandir(self::$fixture_dir));
      foreach($fixture_files as $fixture_file) {
        $table = basename($fixture_file, '.yml');
        $auto_id_column = array_get(self::$auto_id_fixtures, $table, false);
        self::$_fixtures->enqueue_yaml(
          $table,
          self::$fixture_dir.$fixture_file,
          array(
            'auto_id' => $auto_id_column,
            'id_column' => is_string($auto_id_column) ? $auto_id_column : 'id',
            'auto_ref' => true,
            'php_env' => self::$php_env
          )
        );
      }

      do_action('csm_migrate_db');
    }

    $this->dbh = self::$_dbh;
    $this->fixtures = self::$_fixtures;
    $this->which_fixtures = $which_fixtures;
  }

  function setUp() {
    $this->fixtures->load($this->which_fixtures);
  }

  function createInvoker() {
    if(isset($_GET['notrap']))
      return new SimpleInvoker($this);
    else
      return parent::createInvoker($this);
  }

  function env($name) {
    return self::$php_env[$name];
  }

  function assertCount($countable, $count, $msg=null) {
    if(is_int($count)) {
      $min = $count;
      $max = $count;
    } elseif(is_array($count)) {
      $min = isset($count['>']) ? $count['>'] + 1 : 0;
      $min = isset($count['>=']) ? $count['>='] : 0;
      $min = isset($count['min']) ? $count['min'] : 0;
      $max = isset($count['<']) ? $count['<'] - 1 : PHP_INT_MAX;
      $max = isset($count['<=']) ? $count['<='] - 1 : PHP_INT_MAX;
      $max = isset($count['max']) ? $count['max'] - 1 : PHP_INT_MAX;
    }
    $result = $min <= count($countable) && count($countable) <= $max;
    if(is_null($msg) && ! $result) {
      $msg = 'Expexted ';
      if($min == $max)
        $msg .= 'count == '. $min;
      elseif(0 == $min)
        $msg .= 'count <= '.$max;
      elseif(PHP_INT_MAX == $max)
        $msg .= 'count >= '.$min;
      else
        $msg .= $min.' <= count <= '.$max;
      $msg .= ' [got: '.count($countable).']';
    }

    return $this->assertTrue($result, $msg);
  }

  function auto_id($name) {
    return SimpleFixtures::get_auto_id($name);
  }
}
?>

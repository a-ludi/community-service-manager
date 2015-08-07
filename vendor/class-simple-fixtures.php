<?php
/* Copyright © 2014 Arne Ludwig <arne.ludwig@posteo.de>
 *
 * This program is free software: you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program. If
 * not, see <http://www.gnu.org/licenses/>.
 */

class SimpleFixtures {
  protected $dbh;
  protected $fixture_sets;
  protected $fixture_columns;
  protected $stmts;

  public function __construct($dbh) {
    $this->dbh = $dbh;
    $this->fixture_sets = array();
    $this->fixture_columns = array();
    $this->stmts = array();
  }

  public function load($tables=null) {
    if(is_null($tables))
      $tables = array_keys($this->fixture_sets);
    elseif(! is_array($tables))
      $tables = array_map('strtolower', func_get_args());
    else
      $tables = array_map('strtolower', $tables);

    foreach($tables as $table)
      $this->load_table($table);
  }

  protected function load_table($table) {
    if(! isset($this->fixture_sets[$table])) {
      trigger_error(
        "Cannot load fixtures for table `$table`: non enqueued",
        E_USER_NOTICE
      );
      return;
    }

    $fixtures = $this->fixture_sets[$table];
    if(count($fixtures) === 0)
      return 0;
    if(! isset($this->fixture_columns[$table]))
      $this->fixture_columns[$table] = $this->get_fixture_columns($table);
    if(! $this->table_column_names_valid($table))
      trigger_error('Column names must only constist of [a-zA-Z0-9_]', E_USER_WARNING);
    if(! isset($this->stmts[$table])) {
      $this->stmts[$table] = $this->dbh->prepare(
        $this->build_insert_query($table)
      );
      if(! $this->stmts[$table])
        trigger_error(
          "Cannot insert fixtures into table `$table`: ".$this->build_error_msg(),
          E_USER_WARNING
        );
    }

    $this->dbh->beginTransaction();
    if(! $this->clear_table($table))
      trigger_error(
        "Could not clear table `$table`: ".$this->build_error_msg(),
        E_USER_WARNING
      );
    foreach($fixtures as $fixture) {
      $row = $this->prepare_row_from_fixture($fixture);
      $this->stmts[$table]->execute($row);
    }
    $this->dbh->commit();
  }

  protected function get_fixture_columns($table) {
    $fixture_set = $this->fixture_sets[$table];
    return array_unique(
      array_reduce($fixture_set, function($cols, $row) {
        return array_merge($cols, array_map('strtolower', array_keys($row)));
      }, array())
    );
  }

  protected function prepare_row_from_fixture($fixture) {
    $row = array();
    foreach($fixture as $column => $value)
      $row[':'.strtolower($column)] = $value;

    return $row;
  }

  protected function clear_table($table) {
    $result = $this->dbh->query("DELETE FROM `$table`");
    if($result)
      $result->closeCursor();

    return (bool) $result;
  }

  protected function table_column_names_valid($table) {
    $invalid_columns = preg_grep(
      '/^[a-zA-Z0-9_]+$/',
      $this->fixture_columns[$table],
      PREG_GREP_INVERT
    );

    return 0 == count($invalid_columns);
  }

  protected function build_insert_query($table) {
    $columns = '`'.implode('`,`', $this->fixture_columns[$table]).'`';
    $value_placeholders = ':'.implode(',:', $this->fixture_columns[$table]);
    $query = "INSERT INTO `$table` ($columns) VALUES ($value_placeholders)";
    
    return $query;
  }

  public function enqueue($table, $data, $options=array()) {
    $table = strtolower($table);
    unset($this->fixture_columns[$table]);
    unset($this->stmts[$table]);

    if(! isset($options['auto_id']) || $options['auto_id'])
      $this->augment_with_auto_id(
        $data,
        isset($options['auto_id']) ? $options['auto_id'] : 'id'
      );
    
    if(! isset($options['auto_ref']) || $options['auto_ref'])
      $this->replace_auto_ref($data);
    
    if(! isset($options['merge']) || ! $options['merge'])
      $this->set_fixture_data($table, $data);
    else
      $this->merge_fixture_data($table, $data);
  }

  protected function augment_with_auto_id(&$data, $id_col) {
    foreach($data as $name => $row)
      if(! isset($data[$name][$id_col]))
        $data[$name][$id_col] = self::get_auto_id($name);
  }

  protected function replace_auto_ref(&$data) {
    foreach($data as $name => $row) {
      foreach($row as $col => $value) {
        if(is_string($value) && strlen($value) >= 2)
          if($value[0] === '\\' && $value[1] === '&')
            $data[$name][$col] = substr($value, 1);
          elseif($value[0] === '&')
            $data[$name][$col] = self::get_auto_id(substr($value, 1));
      }
    }
  }

  protected function merge_fixture_data($table, &$data) {
    if(! isset($this->fixture_sets[$table]))
      $this->fixture_sets[$table] = array();
    $this->fixture_sets[$table] = array_merge($this->fixture_sets[$table], $data);
  }

  protected function set_fixture_data($table, &$data) {
    $this->fixture_sets[$table] = $data;
  }

  protected function build_error_msg($obj=null) {
    if(is_null($obj))
      $obj = $this->dbh;

    list($sql_code, $driver_code, $msg) = $obj->errorInfo();

    return "$msg ($sql_code/$driver_code)";
  }

  public static function get_auto_id($name) {
    return intval(hash('crc32', $name), 16);
  }
}

if(in_array('yaml', get_loaded_extensions())) {
  class YamlFixtures extends SimpleFixtures {
    protected $yaml_env = null;

    public function enqueue_yaml($yaml_file, $options=array()) {
      $this->yaml_env = isset($options['yaml_env']) ?
        $options['yaml_env'] :
        null;
      $ndocs = 0;
      $data = yaml_parse_file(
        $yaml_file,
        0,
        $ndocs,
        array('!php/eval' => array(__CLASS__, 'cb_yaml_php_eval'))
      );
      if($ndocs > 1)
        trigger_error(
          "The YAML file constitutes more than one document; ".
          "ignoring $ndocs surplus docs",
          E_USER_NOTICE
        );

      parent::enqueue(
        $this->infer_table_name($yaml_file, $options),
        $data,
        $options
      );
    }

    protected function infer_table_name($yaml_file, $options) {
      if(isset($options['table']))
        return $options['table'];
      else
        return basename($yaml_file, '.yml');
    }

    public function cb_yaml_php_eval($value, $tag, $flags) {
      if(! is_null($this->yaml_env))
        extract($this->yaml_env);
      return eval($value);
    }
  }
}


if(php_sapi_name() == 'cli') {
  class TestDB extends PDO {
    static $instance = null;

    public static function get_instance($reset=true) {
      if(is_null(self::$instance))
        self::$instance = new self();
      if($reset)
        self::$instance->reset_tables();

      return self::$instance;
    }

    public function __construct() {
      parent::__construct("sqlite::memory:");
      $this->create_tables();
    }

    protected function create_tables() {
      $this->query(<<<SQL
        CREATE TABLE users (
          id               integer NOT NULL  PRIMARY KEY AUTOINCREMENT,
          login            varchar(32),
          passwdhash       char(32),
          created_at       bigint NOT NULL
        )
SQL
      );

      $this->query(<<<SQL
        CREATE TABLE user_friends (
          user_id          bigint unsigned NOT NULL,
          friend_id        bigint unsigned NOT NULL
        )
SQL
      );
    }

    protected function reset_tables() {
      $this->query('DELETE FROM users');
      $this->query('DELETE FROM user_friends');
      $this->query('UPDATE sqlite_sequence SET seq = 0 WHERE 1');
    }

    public function get_test_data($table=null) {
      $now = time();
      $test_data = array(
        'users' => array(
            'bert' => array(
              'login' => 'bert',
              'passwdhash' => md5('sekret'), // don't use md5() in production!!
              'created_at' => $now - 7*SECS_PER_DAY),
            'ernie' => array(
              'login' => 'ernie',
              'passwdhash' => md5('p455w0rd'), // don't use md5() in production!!
              'created_at' => $now - 7*SECS_PER_DAY)),
        'user_friends' => array(
            array(
              'user_id' => '&ernie',
              'friend_id' => '&bert')));

      if(is_string($table))
        return $test_data[$table];
      else
        return $test_data;
    }

    public function get_test_file_yaml($table, $fname=null) {
      if(is_null($fname))
        $fname = "$table.yml";

      $fname = sys_get_temp_dir()."/$fname";
      $fd = fopen($fname, 'w');
      if(! $fd) {
        trigger_error("could not open temporary file `$fname`", E_USER_ERROR);
        return null;
      }

      switch($table) {
        case 'users':
          fwrite($fd, <<<YAML
bert:
  login: bert
  passwdhash: !php/eval return(md5('sekret'));
  created_at: !php/eval return(\$now - 7*SECS_PER_DAY);

ernie:
  login: ernie
  passwdhash: !php/eval return(md5('p455w0rd'));
  created_at: !php/eval return(\$now - 7*SECS_PER_DAY);
YAML
          );
          break;
        case 'user_friends':
          fwrite($fd, <<<YAML
-
  user_id: &ernie
  friend_id: &bert
YAML
          );
          break;
        default:
          return null;
      }
      fclose($fd);

      return $fname;
    }
  }

  define('SECS_PER_DAY', 60*60*24);

  function all(&$arr) {
    foreach ($arr as $value)
      if(! $value)
        return false;
    return true;
  }

  function not($bool) {
    return ! $bool;
  }

  function assert_error($callback, $error_no=null) {
    $error = false;
    $old_error_handler = set_error_handler(function ($curr_error_no, $error_string) use (&$error, $error_no) {
      $expected = is_null($error_no) || $error_no == $expected_error_no;
      $error = $error || $expected;
      $expected or print("Error: $error_string".PHP_EOL);
    });
    
    call_user_func($callback);
    
    set_error_handler($old_error_handler);

    return $error;
  }

  $unitTests = array();
  
  $unitTests['Get Auto ID'] = function() {
    $auto_id1 = SimpleFixtures::get_auto_id('foobar');
    $auto_id2 = SimpleFixtures::get_auto_id('foobar');

    return array(
      'auto_id_is_int' => is_int($auto_id1) && is_int($auto_id2),
      'auto_id_is_deterministic' => $auto_id1 === $auto_id2
    );
  };
  
  $unitTests['Insert Fixtures With Auto ID'] = function() {
    $dbh = TestDB::get_instance();
    $data = $dbh->get_test_data('users');
    $fixtures = new SimpleFixtures($dbh);
    $fixtures->enqueue('users', $data);
    $fixtures->load('users');
    $users = $dbh->
      query('SELECT * FROM users ORDER BY login ASC')->
      fetchAll(PDO::FETCH_ASSOC);

    return array(
      'inserted_both_rows' => count($users) === 2,
      'inserted_bert' => array_diff_key($users[0], array('id' => false)) == $data['bert'],
      'augmented_with_auto_id' => all(array_map(function($row) {
        return isset($row['id']) &&
               $row['id'] == SimpleFixtures::get_auto_id($row['login']);
      }, $users))
    );
  };
  
  $unitTests['Insert Fixtures Without Auto ID'] = function() {
    $dbh = TestDB::get_instance();
    $data = $dbh->get_test_data('users');
    $fixtures = new SimpleFixtures($dbh);
    $fixtures->enqueue('users', $data, array('auto_id' => false));
    $fixtures->load('users');
    $users = $dbh->
      query('SELECT * FROM users ORDER BY login ASC')->
      fetchAll(PDO::FETCH_ASSOC);

    return array(
      'inserted_both_rows' => count($users) === 2,
      'inserted_bert' => array_diff_key($users[0], array('id' => false)) == $data['bert'],
      'sequential_id' => all(array_map(function($row, $seq_id) {
        return isset($row['id']) &&
               $row['id'] == $seq_id;
      }, $users, range(1, count($users))))
    );
  };
  
  $unitTests['Insert Fixtures With Auto Ref'] = function() {
    $dbh = TestDB::get_instance();
    $data = $dbh->get_test_data('user_friends');
    $fixtures = new SimpleFixtures($dbh);
    $fixtures->enqueue('user_friends', $data, array('auto_id' => false));
    $fixtures->load('user_friends');
    $user_friends = $dbh->
      query('SELECT * FROM user_friends')->
      fetchAll(PDO::FETCH_ASSOC);

    return array(
      'inserted_row' => count($user_friends) === 1,
      'replaced_auto_ref' =>
        $user_friends[0]['user_id'] == SimpleFixtures::get_auto_id('ernie') &&
        $user_friends[0]['friend_id'] == SimpleFixtures::get_auto_id('bert')
    );
  };
  
  $unitTests['Insert Merged Fixtures'] = function() {
    $dbh = TestDB::get_instance();
    $data = $dbh->get_test_data('users');
    $fixtures = new SimpleFixtures($dbh);
    foreach($data as $name => $fixture)
      $fixtures->enqueue(
        'users',
        array($name => $fixture),
        array('merge' => true)
      );
    $fixtures->load('users');
    $users = $dbh->
      query('SELECT * FROM users ORDER BY login ASC')->
      fetchAll(PDO::FETCH_ASSOC);

    return array(
      'inserted_both_rows' => count($users) === 2,
      'inserted_bert' => array_diff_key($users[0], array('id' => false)) == $data['bert'],
      'augmented_with_auto_id' => all(array_map(function($row) {
        return isset($row['id']) &&
               $row['id'] == SimpleFixtures::get_auto_id($row['login']);
      }, $users))
    );
  };
  
  $unitTests['Insert Multiple Fixture Sets'] = function() {
    $dbh = TestDB::get_instance();
    $data = $dbh->get_test_data();
    $fixtures = new SimpleFixtures($dbh);
    $fixtures->enqueue('users', $data['users']);
    $fixtures->enqueue(
      'user_friends',
      $data['user_friends'],
      array('auto_id' => false)
    );

    $fixtures->load('users', 'user_friends');
    $users = $dbh->
      query('SELECT * FROM users')->
      fetchAll(PDO::FETCH_ASSOC);
    $user_friends = $dbh->
      query('SELECT * FROM user_friends')->
      fetchAll(PDO::FETCH_ASSOC);

    return array(
      'inserted_rows' => count($users) === 2 && count($user_friends) === 1
    );
  };
  
  $unitTests['Insert All Fixture Sets'] = function() {
    $dbh = TestDB::get_instance();
    $data = $dbh->get_test_data();
    $fixtures = new SimpleFixtures($dbh);
    $fixtures->enqueue('users', $data['users']);
    $fixtures->enqueue(
      'user_friends',
      $data['user_friends'],
      array('auto_id' => false)
    );

    $fixtures->load('users', 'user_friends');
    $users = $dbh->
      query('SELECT * FROM users')->
      fetchAll(PDO::FETCH_ASSOC);
    $user_friends = $dbh->
      query('SELECT * FROM user_friends')->
      fetchAll(PDO::FETCH_ASSOC);

    return array(
      'inserted_rows' => count($users) === 2 && count($user_friends) === 1
    );
  };
  
  $unitTests['Insert Partial Fixture'] = function() {
    $dbh = TestDB::get_instance();
    $data = $dbh->get_test_data('users');
    unset($data['ernie']);
    unset($data['bert']['login']);
    $fixtures = new SimpleFixtures($dbh);
    $fixtures->enqueue('users', $data);

    $fixtures->load();
    $users = $dbh->
      query('SELECT * FROM users')->
      fetchAll(PDO::FETCH_ASSOC);

    return array(
      'inserted_bert' => array_diff_key($users[0], array('id' => false, 'login' => false)) == $data['bert'],
      'login_is_null' => $users[0]['login'] == null
    );
  };
  
  $unitTests['Clears Table Before Insert'] = function() {
    $dbh = TestDB::get_instance();
    $data = $dbh->get_test_data('users');
    $fixtures = new SimpleFixtures($dbh);
    $fixtures->enqueue('users', $data);

    $dbh->query('INSERT INTO users (login, created_at) VALUES ("cookie_monster", 0)');
    $fixtures->load();
    $users = $dbh->
      query('SELECT * FROM users')->
      fetchAll(PDO::FETCH_ASSOC);

    return array(
      'have_two_users' => count($users) === 2,
      'dont_have_cookie_monster' => all(array_map(function($row) {
        return $row['login'] != 'cookie_monster';
      }, $users))
    );
  };

  if(in_array('yaml', get_loaded_extensions())) {
    $unitTests['Insert YAML Fixtures With Auto Table'] = function() {
      $dbh = TestDB::get_instance();
      $yaml_file = $dbh->get_test_file_yaml('users');
      $fixtures = new YamlFixtures($dbh);
      $fixtures->enqueue_yaml($yaml_file, array(
        'yaml_env' => array('now' => time())
      ));
      $fixtures->load('users');
      $users = $dbh->
        query('SELECT * FROM users ORDER BY login ASC')->
        fetchAll(PDO::FETCH_ASSOC);

      return array(
        'inserted_both_rows' => count($users) === 2,
        'inserted_bert' => $users[0]['login'] == 'bert'
      );
    };

    $unitTests['Insert YAML Fixtures With Manual Table'] = function() {
      $dbh = TestDB::get_instance();
      $yaml_file = $dbh->get_test_file_yaml('users', 'fixtures.yml');
      $fixtures = new YamlFixtures($dbh);
      $fixtures->enqueue_yaml($yaml_file, array(
        'table' => 'users',
        'yaml_env' => array('now' => time())
      ));
      $fixtures->load('users');
      $users = $dbh->
        query('SELECT * FROM users ORDER BY login ASC')->
        fetchAll(PDO::FETCH_ASSOC);

      return array(
        'inserted_both_rows' => count($users) === 2,
        'inserted_bert' => $users[0]['login'] == 'bert'
      );
    };

    $unitTests['Insert YAML Fixtures With PHP Eval'] = function() {
      $dbh = TestDB::get_instance();
      $yaml_file = $dbh->get_test_file_yaml('users');
      $now = time();
      $fixtures = new YamlFixtures($dbh);
      $fixtures->enqueue_yaml($yaml_file, array(
        'yaml_env' => array('now' => $now)
      ));
      $fixtures->load('users');
      $users = $dbh->
        query('SELECT * FROM users ORDER BY login ASC LIMIT 1')->
        fetchAll(PDO::FETCH_ASSOC);

      return array(
        'evaled_created_at' => $users[0]['created_at'] == $now - 7*SECS_PER_DAY
      );
    };
  }

  
  $failures = array();
  $passedCount = 0;
  foreach($unitTests as $name => $test) {
    $testResults = call_user_func($test);
    $hasPassed = is_array($testResults) ? all($testResults) : $testResults;
    echo $name . ' Test ... ' . ($hasPassed ? 'passed' : 'failed') . PHP_EOL;
    if($hasPassed)
       $passedCount++;
    else
      $failures[$name] = $testResults;
  }
  
  echo '---' . PHP_EOL;
  echo 'Passed ' . $passedCount . ' of ' . count($unitTests) . ' tests.' . PHP_EOL;
  if(count($failures) > 0) {
    echo PHP_EOL;
    echo 'Failures:'.PHP_EOL;
    foreach($failures as $name => $results) {
      $failedTests = implode(', ', array_keys(array_filter($results, 'not')));
      echo '  failed in ' . $name . ' test: ' . $failedTests . PHP_EOL;
    }
  }

  exit(count($unitTests) - $passedCount);
}
?>
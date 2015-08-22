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

function fail($msg=null) {
  if(is_null($msg))
    $msg = 'Test failed';

  return assert_true(false, $msg);
}

function assert_true($value, $msg=null) {
  if(is_null($msg) && ! $value)
    $msg = 'Expexted true [got: '.mut_safe_to_string($value).']';
  assert((bool) $value, $msg);

  return (bool) $value;
}

function assert_false($value, $msg=null) {
  if(is_null($msg) && ! $value)
    $msg = 'Expexted false [got: '.mut_safe_to_string($value).']';
  return assert_true(! $value, $msg);
}

function assert_null($value, $msg=null) {
  if(is_null($msg) && is_null($value))
    $msg = 'Expexted NULL [got: '.mut_safe_to_string($value).']';
  return assert_true(is_null($value), $msg);
}

function assert_equal($obj1, $obj2, $msg=null) {
  $result = $obj1 == $obj2;
  if(is_null($msg) && ! $result) {
    $msg = 'Expexted equality ';
    $msg .= ' [got: ';
    $msg .= mut_safe_to_string($obj1);
    $msg .= ' != ';
    $msg .= mut_safe_to_string($obj2);
    $msg .= ']';
  }

  return assert_true($result, $msg);
}

function assert_differ($obj1, $obj2, $msg=null) {
  $result = $obj1 != $obj2;
  if(is_null($msg) && ! $result) {
    $msg = 'Expexted difference ';
    $msg .= ' [got: ';
    $msg .= mut_safe_to_string($obj1);
    $msg .= ' == ';
    $msg .= mut_safe_to_string($obj2);
    $msg .= ']';
  }

  return assert_true($result, $msg);
}

function assert_count($ary, $count, $msg=null) {
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
  $result = $min <= count($ary) && count($ary) <= $max;
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
    $msg .= ' [got: '.count($ary).']';
  }

  return assert_true($result, $msg);
}

function expect_error($callback, $error=null, $msg=null) {
  if(is_string($error)) {
    $error = array('match' => $error);
  } elseif(is_int($error)) {
    $error = array('severity' => $error);
  } elseif(! is_array($error)) {
    $error = array();
  }

    $last_error = array(
      'severity' => 0,
      'message' => '',
      'matched' => false,
      'match' => null
    );
  $old_error_handler = set_error_handler(function ($severity, $message) use ($error, &$last_error) {
    $match = array(
      'severity' => ! isset($error['severity']) || $error['severity'] === $severity,
      'message' => ! isset($error['match']) || preg_match($error['match'], $message)
    );

    $last_error = array(
      'severity' => $severity,
      'message' => $message,
      'matched' => $match['severity'] && $match['message'],
      'match' => $match
    );
  });
  call_user_func($callback);
  set_error_handler($old_error_handler);

  if(is_null($msg) && ! $last_error['matched']) {
    $msg = 'Expexted error';
    if(is_null($last_error['match'])) {
      $msg .= ' [got: none]';
    } else {
      if(! $last_error['match']['message']) {
        $msg .= ' matching '.$error['match'];
        $msg .= ' [got: ';
        $msg .= empty($last_error['message']) ?
          'none' :
          $last_error['message'];
        $msg .= ']';
      }
      if(! $last_error['match']['severity']) {
        $msg .= ' with severity ';
        $msg .= join(', ', mut_get_error_names($error['severity']));
        $msg .= ' [got: ';
        $msg .= join(', ', mut_get_error_names($last_error['severity']));
        $msg .= ']';
      }
    }
  }
  return assert_true(! $last_error['matched'], $msg);
}

function mut_safe_to_string($value) {
  switch(gettype($value)) {
    case 'integer':
    case 'double':
    case 'string':
      return (string) $value;
    case 'boolean':
      return $value ? 'true' : 'false';
    case 'NULL':
    case 'resource':
    case 'unknown type':
    case 'array':
      return gettype($value);
    case 'object':
      return get_class($value);
  }
}

function mut_get_error_names($severity) {
  static $known_names = array('E_ERROR', 'E_WARNING', 'E_PARSE', 'E_NOTICE',
    'E_CORE_ERROR', 'E_CORE_WARNING', 'E_COMPILE_ERROR', 'E_COMPILE_WARNING',
    'E_USER_ERROR', 'E_USER_WARNING', 'E_USER_NOTICE', 'E_STRICT',
    'E_RECOVERABLE_ERROR', 'E_DEPRECATED', 'E_USER_DEPRECATED');
  $error_names = array();
  foreach ($known_names as $error_name)
    if($severity & constant($error_name))
      $error_names[] = $error_name;
  
  return $error_names;
}

class MicroTestCase {
  protected $func_name;
  protected $failures;

  public function __construct($func_name) {
    $this->func_name = $func_name;
    $this->failures = array();
  }

  public function run() {
    $old_assert_handler = assert_options(ASSERT_CALLBACK);
    assert_options(ASSERT_CALLBACK, array($this, 'assert_cb'));
    call_user_func($this->func_name);
    assert_options(ASSERT_CALLBACK, $old_assert_handler);
  }

  public function assert_cb($f, $l, $s, $message) {
    $backtrace = debug_backtrace();
    foreach($backtrace as $idx => $stackframe)
      if($stackframe['function'] == $this->func_name)
        break;
    $assertion_frame = $backtrace[$idx-1];

    $this->failures[] = (object) array(
      'backtrace' => $backtrace,
      'file' => isset($assertion_frame['file']) ?
        $assertion_frame['file'] : null,
      'line' => isset($assertion_frame['line']) ?
        $assertion_frame['line'] : null,
      'message' => $message
    );
  }

  public function has_passed() {
    return 0 === count($this->failures);
  }

  public function failures() {
    return $this->failures;
  }

  public function name() {
    return $this->func_name;
  }

  public static function is_test($func_name) {
    return 0 === strpos($func_name, 'test');
  }
}

class MicroUnitTester {
  protected $opts;
  protected $_tests;
  protected $_failed_tests;
  public $test_cases;
  public $passed_tests;
  public $failed_tests;

  public function __construct($options=array()) {
    $this->opts = array_merge(array(
      'cli_colors' => true
    ), $options);
    $this->_tests = get_defined_functions()['user'];
    $this->_tests = array_filter($this->_tests, 'MicroTestCase::is_test');
    $this->_tests = array_map(function($func_name) {
      return new MicroTestCase($func_name);
    }, $this->_tests);
    $this->_failed_tests = array();
    $this->test_cases = count($this->_tests);
    $this->passed_tests = 0;
    $this->failed_tests = 0;
  }

  public function run_tests() {
    $this->_failed_tests = array();
    foreach($this->_tests as $test) {
      $test->run();
      if(! $test->has_passed())
        $this->_failed_tests[] = $test;
    }
    $this->store_statistics();
    $this->report();

    return 0 === $this->failed_tests;
  }

  protected function store_statistics() {
    $this->passed_tests = count($this->_tests) - count($this->_failed_tests);
    $this->failed_tests = count($this->_failed_tests);
  }

  protected function report() {
    foreach($this->_tests as $test) {
      printf(
        "[%s] %s".PHP_EOL,
        $test->has_passed() ? $this->green('pass') : $this->red('FAIL'),
        $test->name()
      );
    }

    printf(str_repeat('-', 79).PHP_EOL);
    printf(
      'Test Cases: %s, Passes: %s, Failures: %s'.PHP_EOL,
      $this->bold($this->test_cases),
      $this->green($this->passed_tests),
      $this->red($this->failed_tests)
    );

    if($this->failed_tests > 0) {
      printf(PHP_EOL);
      printf('Details: '.PHP_EOL);
      foreach($this->_failed_tests as $test)
        $this->report_failure_details($test);
    }
  }

  protected function report_failure_details($test) {
    $failures = $test->failures();
    printf(
      '- %s failed: [%s]'.PHP_EOL,
      $test->name(),
      $this->red(count($failures).' failure(s)')
    );
    foreach($failures as $failure) {
      $out = '';
      if(is_null($failure->file) || is_null($failure->line))
        $out = sprintf('    - %s', $failure->message);
      else
        $out = sprintf(
          '    - %s in %s:%d',
          $failure->message,
          $failure->file,
          $failure->line
        );
      
      printf(self::indented($out, 6).PHP_EOL);
    }
  }

  protected static function indented($str, $cols) {
    return str_replace(PHP_EOL, PHP_EOL.str_repeat(' ', $cols), $str);
  }

  const ANSI_CLEAR = "\033[0m";
  const ANSI_BOLD = "\033[1;37m";
  protected function bold($str) {
    if($this->opts['cli_colors'])
      return self::ANSI_BOLD.$str.self::ANSI_CLEAR;
    else
      return $str;
  }

  const ANSI_RED = "\033[31m";
  protected function red($str) {
    if($this->opts['cli_colors'])
      return self::ANSI_RED.$str.self::ANSI_CLEAR;
    else
      return $str;
  }

  const ANSI_GREEN = "\033[32m";
  protected function green($str) {
    if($this->opts['cli_colors'])
      return self::ANSI_GREEN.$str.self::ANSI_CLEAR;
    else
      return $str;
  }
}
?>
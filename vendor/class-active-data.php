<?php
/* Copyright © 2015 Arne Ludwig <arne.ludwig@posteo.de>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program. If not, see <http://www.gnu.org/licenses/>.
 */

if(! class_exists('ActiveData')) {
  class ActiveData {
    private $data;
    private $properties;

    public function __construct($properties=null) {
      $this->data = array();
      $this->properties = is_array($properties) ?
        array_values($properties) :
        null;
    }

    public function __get($name) {
      if(! $this->is_set($name))
        return $this->undefined_property($name);

      if(! isset($this->data[$name]))
        $this->data[$name] = $this->{"get_default_$name"}();

      return $this->data[$name];
    }

    public function __set($name, $value) {
      if(! is_null($this->properties) && ! in_array($name, $this->properties))
        return $this->undefined_property($name);

      if(method_exists($this, "filter_$name"))
        $this->data[$name] = $this->{"filter_$name"}($value);
      else
        $this->data[$name] = $value;
    }

    public function set_data($data) {
      foreach($data as $name => $value)
        $this->$name = $value;
    }

    public function is_set($name) {
      return isset($this->data[$name]) ||
             method_exists($this, "get_default_$name");
    }

    // only PHP >=5.0
    public function __isset($name) {
      return $this->is_set($name);
    }

    protected function undefined_property($name) {
      trigger_error(
        'Undefined property: '.__CLASS__.'::'.$name,
        E_USER_NOTICE
      );
    }

    protected function get_raw_data() {
      return $this->data;
    }

    protected function set_raw_data($data) {
      $this->data = $data;
    }

    protected function clear_property($name) {
      unset($this->data[$name]);
    }
  }

  if(php_sapi_name() == 'cli') {
    class TestActiveData extends ActiveData {
      public function __construct($names=null) {
        parent::__construct($names);
      }

      protected function get_default_foo() {
        return 'foo';
      }

      protected function get_baz() {
        return 'baz';
      }

      protected function filter_foo($foo) {
        return is_string($foo) ? $foo : 'foo';
      }

      public function force_set($data) {
        $this->set_raw_data($data);
      }

      public function clear_foo() {
        $this->clear_property('foo');
      }
    }

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
    
    $unitTests['Get Property'] = function() {
      $c = new TestActiveData();
      $c->force_set(array('foo' => 'foo', 'bar' => 'bar'));

      return array(
        'get_foo' => $c->foo === 'foo',
        'get_bar' => $c->bar === 'bar'
      );
    };

    $unitTests['Get Default Property'] = function() {
      $c = new TestActiveData();

      return $c->foo === 'foo';
    };
    
    $unitTests['Get Undefined Property'] = function() {
      $c = new TestActiveData();

      return assert_error(function() use ($c) {
        $c->bar;
      });
    };
    
    $unitTests['Set Property'] = function() {
      $c = new TestActiveData();
      $c->foo = 'foo';
      $c->bar = 'bar';

      return array(
        'get_foo' => $c->foo === 'foo',
        'get_bar' => $c->bar === 'bar'
      );
    };
    
    $unitTests['Set Data'] = function() {
      $c = new TestActiveData();
      $c->set_data(array(
        'foo' => 'foo',
        'bar' => 'bar'
      ));

      return array(
        'get_foo' => $c->foo === 'foo',
        'get_bar' => $c->bar === 'bar'
      );
    };
    
    $unitTests['Restrict Properties'] = function() {
      $c = new TestActiveData(array('foo'));

      return array(
        'without_error' => ! assert_error(function() use ($c) {
          $c->foo = 'foo';
        }),
        'with_error' =>  assert_error(function() use ($c) {
          $c->bar = 'bar';
        })
      );
    };

    $unitTests['Is Property Set'] = function() {
      $c = new TestActiveData();

      return array(
        'is_set_foo' => $c->is_set('foo'),
        'is_set_bar' => ! $c->is_set('bar')
      );
    };

    $unitTests['Clear Property'] = function() {
      $c = new TestActiveData();
      $c->foo = 'bar';
      $changed_property = $c->foo === 'bar';
      $c->clear_foo();

      return array(
        'changed_property' => $changed_property,
        'default_property' => $c->foo === 'foo'
      );
    };

    
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
}
?>

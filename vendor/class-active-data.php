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
    require_once 'class-micro-unit-tester.php';

    class TestActiveData extends ActiveData {
      public function __construct($names=null) {
        parent::__construct($names);
      }

      protected function get_default_foo() {
        return 'foo';
      }

      protected function get_default_foobar() {
        return empty($this->foo) ? 'bar' : 'foo';
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

    function test_get_property() {
      $c = new TestActiveData();
      $c->force_set(array('foo' => 'foo', 'bar' => 'bar'));

      assert_identical($c->foo, 'foo');
      assert_identical($c->bar, 'bar');
    }

    function test_get_default_property() {
      $c = new TestActiveData();

      assert_identical($c->foo, 'foo');
    }
    
    function test_get_undefined_property() {
      $c = new TestActiveData();

      expect_error(function() use ($c) {
        $c->bar;
      });
    }
    
    function test_set_property() {
      $c = new TestActiveData();
      $c->foo = 'new_foo';
      $c->bar = 'new_bar';

      assert_identical($c->foo, 'new_foo');
      assert_identical($c->bar, 'new_bar');
    }
    
    function test_set_data() {
      $c = new TestActiveData();
      $c->set_data(array(
        'foo' => 'new_foo',
        'bar' => 'new_bar'
      ));

      assert_identical($c->foo, 'new_foo');
      assert_identical($c->bar, 'new_bar');
    }
    
    function test_restrict_properties() {
      $c = new TestActiveData(array('foo'));

      expect_no_error(function() use ($c) {
          $c->foo = 'foo';
      });
      expect_error(function() use ($c) {
          $c->bar = 'bar';
      });
    }

    function test_is_property_set() {
      $c = new TestActiveData();

      assert_true($c->is_set('foo'));
      assert_false($c->is_set('bar'));
    }

    function test_clear_property() {
      $c = new TestActiveData();
      $c->foo = 'new_foo';
      assert_identical($c->foo, 'new_foo');

      $c->clear_foo();
      assert_identical($c->foo, 'foo');
    };

    function test_save_default_value() {
      $c = new TestActiveData();
      assert_identical($c->foobar, 'foo');
      $c->foo = '';
      assert_identical($c->foobar, 'foo');

      $c = new TestActiveData();
      $c->foo = '';
      assert_identical($c->foobar, 'bar');
      $c->foo = 'non-empty';
      assert_identical($c->foobar, 'bar');
    };

    $tester = new MicroUnitTester();
    $tester->run_tests();

    exit($tester->failed_tests);
  }
}
?>

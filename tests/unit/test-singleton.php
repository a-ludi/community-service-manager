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

class MockClass {
  public $data;

  public function __construct($data=null) {
    $this->data = $data;
  }
}

class TestSingleton extends CSM_UnitTestCase {
  function __construct() {
    parent::__construct(array());
  }

  function test_register_stores_object_under_class_name() {
    CSM_Singleton::register(new MockClass());
    $this->assertIsA(CSM_Singleton::get('MockClass'), 'MockClass');
  }

  function test_get_returns_the_registered_instance() {
    $obj = new MockClass(rand());
    CSM_Singleton::register($obj);
    $this->assertIdentical(CSM_Singleton::get('MockClass'), $obj);
  }

  function test_register_overwrites_current_instance() {
    $obj1 = new MockClass(rand());
    $obj2 = new MockClass(rand());
    CSM_Singleton::register($obj1);
    CSM_Singleton::register($obj2);
    $this->assertIdentical(CSM_Singleton::get('MockClass'), $obj2);
  }

  function test_register_triggers_error_if_class_name_not_registered() {
    $this->expectError();
    $this->assertNull(CSM_Singleton::get('NotRegistered'));
  }
}
?>

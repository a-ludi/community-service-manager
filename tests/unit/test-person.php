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

Mock::generatePartial(
  'CSM_Person',
  'CSM_MockPerson',
  array(
    // additional methods go here
  )
);
class TestPerson extends CSM_UnitTestCase {
  use DateTimeAssertions;

  function __construct() {
    parent::__construct(array(
      'wp_users',
      'wp_usermeta'
    ));
  }

  function setUp() {
    parent::setUp();
    $this->person = new CSM_MockPerson();
    $this->person->slug = 'root';
  }

  function assert_attr_read_only($obj, $attr_name, $test_value, $msg=null) {
    if(is_null($msg))
      $msg = 'Exepected %s not to change but [got: %s] after setting [to: %s] [from: %s]';

    $old_value = @$obj->$attr_name;
    $this->expectError();
    $obj->$attr_name = $test_value;
    $new_value = @$obj->$attr_name;

    $dumper = new SimpleDumper();
    $msg = sprintf(
      $msg,
      get_class($obj).'::$'.$attr_name,
      $dumper->describeValue($new_value),
      $dumper->describeValue($test_value),
      $dumper->describeValue($old_value)
    );
    $this->assertEqual($old_value, $new_value, $msg);
  }

  function test_person_exists() {
    $this->assertTrue(
      class_exists('CSM_Person'),
      'Class CSM_Person should exist'
    );
  }

  function test_set_and_get_slug() {
    $this->person->slug = 'volunteer1';
    $this->assertEqual($this->person->slug, 'volunteer1');
  }

  function test_first_name_is_read_only() {
    $this->assert_attr_read_only($this->person, 'first_name', 'Chuck');
  }

  function test_last_name_is_read_only() {
    $this->assert_attr_read_only($this->person, 'last_name', 'Norris');
  }

  function test_full_name_is_read_only() {
    $this->assert_attr_read_only($this->person, 'full_name', 'Chuck Norris');
  }

  function test_display_name_is_read_only() {
    $this->assert_attr_read_only($this->person, 'display_name', 'Walker');
  }

  function test_contact_methods_are_read_only() {
    $this->assert_attr_read_only($this->person, 'contact_methods', array());
  }

  function test_default_value_for_first_name() {
    $this->assertEqual($this->person->first_name, 'John');
  }

  function test_default_value_for_last_name() {
    $this->assertEqual($this->person->last_name, 'Doe');
  }

  function test_default_value_for_full_name() {
    // NOTE: this may depend on the current locale/language
    $this->assertEqual($this->person->full_name, 'John Doe');
  }

  function test_default_value_for_display_name() {
    $this->assertEqual($this->person->display_name, 'root');
  }

  function test_default_value_for_contact_methods() {
    $this->assertEqual(
      $this->person->contact_methods,
      array('email' => 'root@example.com')
    );
  }

  function test_default_value_updates_after_slug_change_for_first_name() {
    $this->person->first_name;
    $this->person->slug = 'volunteer1';
    $this->assertEqual($this->person->first_name, 'Johann Sebastian');
  }

  function test_default_value_updates_after_slug_change_for_last_name() {
    $this->person->last_name;
    $this->person->slug = 'volunteer1';
    $this->assertEqual($this->person->last_name, 'Bach');
  }

  function test_default_value_updates_after_slug_change_for_full_name() {
    $this->person->full_name;
    $this->person->slug = 'volunteer1';
    // NOTE: this may depend on the current locale/language
    $this->assertEqual($this->person->full_name, 'Johann Sebastian Bach');
  }

  function test_default_value_updates_after_slug_change_for_display_name() {
    $this->person->display_name;
    $this->person->slug = 'volunteer1';
    $this->assertEqual($this->person->display_name, 'volunteer1');
  }

  function test_default_value_updates_after_slug_change_for_contact_methods() {
    $this->person->contact_methods;
    $this->person->slug = 'volunteer1';
    $this->assertEqual(
      $this->person->contact_methods,
      array('email' => 'volunteer1@example.com')
    );
  }
}
?>

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
  }

  function test_person_exists() {
    $this->assertTrue(
      class_exists('CSM_Person'),
      'Class CSM_Person should exist'
    );
  }

  function test_set_and_get_slug() {
    $this->person->slug = 'root';
    $this->assertEqual($this->person->slug, 'root');
  }

  function test_set_and_get_first_name() {
    $this->person->first_name = 'John';
    $this->assertEqual($this->person->first_name, 'John');
  }

  function test_set_and_get_last_name() {
    $this->person->last_name = 'Doe';
    $this->assertEqual($this->person->last_name, 'Doe');
  }

  function test_set_and_get_display_name() {
    $this->person->display_name = 'Johnny';
    $this->assertEqual($this->person->display_name, 'Johnny');
  }

  function test_set_and_get_contact_methods() {
    $this->person->contact_methods = array('email' => 'root@example.com');
    $this->assertEqual(
      $this->person->contact_methods,
      array('email' => 'root@example.com')
      );
  }
}
?>

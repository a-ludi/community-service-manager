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

class TestContactMethods extends CSM_UnitTestCase {
  function __construct() {
    parent::__construct(array(
      'wp_users',
      'wp_usermeta'
    ));
  }

  function setUp() {
    parent::setUp();
    add_filter('user_contactmethods', function ($methods, $user=null) {
      $methods['phone_home'] = 'Phone (Home)';
      return $methods;
    });
    $this->available_contact_methods = array(
      'email' => __('E-mail'),
      'phone_home' => 'Phone (Home)'
    );
  }

  function test_get_available_returns_correct_methods() {
    $this->assertEqual(
      CSM_ContactMethods::get_available(),
      array_keys($this->available_contact_methods)
    );
  }

  function test_get_label_returns_correct_labels() {
    foreach($this->available_contact_methods as $name => $label)
      $this->assertEqual(
        CSM_ContactMethods::get_label($name),
        $label
      );
  }

  function test_get_label_with_unknown_contact_method() {
    $this->expectError();
    $label = CSM_ContactMethods::get_label('unknown_method');
    $this->assertNull($label);
  }

  function test_get_availble_contact_method_values() {
    $contact_methods = new CSM_ContactMethods('root');
    $this->assertEqual($contact_methods->email, 'root@example.com');
    $this->assertEqual($contact_methods->phone_home, '+00 0000 000000');
    $contact_methods = new CSM_ContactMethods('volunteer1');
    $this->assertEqual($contact_methods->email, 'volunteer1@example.com');
    $this->assertEqual($contact_methods->phone_home, null);
  }

  function test_get_unavailble_contact_method_values() {
    $contact_methods = new CSM_ContactMethods('root');
    $this->expectError();
    $value = $contact_methods->unknown_method;
    $this->assertNull($value);
  }
}
?>

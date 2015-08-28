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

class CSM_ContactMethods extends ActiveData {
  protected $wp_user;

  public function __construct($person_slug) {
    $this->wp_user = get_user_by('login', $person_slug);
  }

  protected static function build_available_user_contact_methods() {
    return array_merge(
      array('email' => __('E-mail')),
      wp_get_user_contact_methods()
    );
  }

  public static function get_available() {
    return array_keys(self::build_available_user_contact_methods());
  }

  public static function get_label($contact_method) {
    $available_methods = self::build_available_user_contact_methods();
    if(isset($available_methods[$contact_method])) {
      return $available_methods[$contact_method];
    } else {
      return self::trigger_method_not_registered($contact_method);
    }
  }

  public function __get($name) {
    return in_array($name, self::get_available()) ?
      $this->get_method_value($name) :
      self::trigger_method_not_registered($name);
  }

  protected function get_method_value($name) {
    if('email' == $name)
      return $this->wp_user->user_email;
    else
      return $this->wp_user->$name;
  }

  protected static function trigger_method_not_registered($contact_method) {
    trigger_error(
      "User contact method '$contact_method' is not registered",
      E_USER_NOTICE
    );
    return null;
  }
}
?>

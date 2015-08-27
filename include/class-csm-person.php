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

abstract class CSM_Person extends ActiveData {
  static $properties = array(
    'slug',
    'first_name',
    'last_name',
    'full_name',
    'display_name',
    'contact_methods'
  );
  protected $wp_user;

  public function __construct($slug) {
    parent::__construct(self::$properties);
    $this->slug = $slug;
  }

  protected function filter_slug($slug) {
    $this->wp_user = get_user_by('login', (string) $slug);
    // TODO clear read-only attributes
    return (string) $slug;
  }

  protected function filter_first_name() {
    return $this->attribute_read_only('first_name');
  }

  protected function filter_last_name() {
    return $this->attribute_read_only('last_name');
  }

  protected function filter_full_name() {
    return $this->attribute_read_only('full_name');
  }

  protected function filter_display_name() {
    return $this->attribute_read_only('display_name');
  }

  protected function filter_contact_methods() {
    return $this->attribute_read_only('contact_methods');
  }

  protected function get_default_first_name() {
    return $this->wp_user ? $this->wp_user->first_name : null;
  }

  protected function get_default_last_name() {
    return $this->wp_user ? $this->wp_user->last_name : null;
  }

  protected function get_default_full_name() {
    $name_parts = array_filter(array($this->first_name, $this->last_name));
    return join(' ', $name_parts);
  }

  protected function get_default_display_name() {
    return $this->wp_user ? $this->wp_user->display_name : null;
  }

  protected function get_default_contact_methods() {
    // TODO return CSM_ContactMethods
    return null;
  }

  protected function attribute_read_only($attr) {
    trigger_error(
      'Attribute '.__CLASS__.'::$'.$attr.' is read-only',
      E_USER_WARNING
    );

    return $this->$attr;
  }
}
?>

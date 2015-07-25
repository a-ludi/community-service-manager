<?php
/* Copyright © 2014 Arne Ludwig <arne.ludwig@posteo.de>
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

class CSM_JournalEntry extends ActiveData {
  static $properties = array(
    'id',
    'shift_slug',
    'shift_duration',
    'volunteer_slug',
    'volunteers_count',
    'is_frozen',
    'created_at',
    'updated_at'
  );

  /**
   * Construct a journal entry from the given information. The additional
   * $details array may contain the following fields:
   *
   * - __id:__ (int) The ID of this entry.
   * - __shift_slug:__ (string) Slug of the shift.
   * - __shift_duration:__ (SimpleTimeInterval|int) Duration of the shift. TODO
   * - __volunteer_slug:__ (string) Slug of the volunteer.
   * - __volunteers_count:__ (int) The number of volunteers available for the
   *   shift.
   * - __is_frozen:__ (boolean) True if this record must not be modified.
   * - __created_at:__ (SimpleDateTime|DateTime|int) The date and time of
   *   creation.
   * - __updated_at:__ (SimpleDateTime|DateTime|int) The date and time of the
   *   last update.
   */
  public function __construct($values=array()) {
    parent::__construct(self::$properties);
    if(is_array($values))
      $this->set_data($values);
  }

  protected function filter_id($value) {
    return is_null($value) ? null : (int) $value;
  }
  
  protected function filter_shift_duration($value) {
    if($value instanceof SimpleTimeInterval)
      return $value;
    else
      return new SimpleTimeInterval(max(0, (int) $value));
  }

  protected function filter_shift_slug($value) {
    $this->clear_property('shift');
    return (string) $value;
  }

  protected function filter_volunteer_slug($value) {
    $this->clear_property('volunteer');
    return (string) $value;
  }

  protected function filter_volunteers_count($value) {
    return max(0, (int) $value);
  }

  protected function filter_is_frozen($value) {
    return (boolean) $value;
  }

  protected function filter_created_at($value) {
    return $this->filter_date($value);
  }

  protected function filter_updated_at($value) {
    return $this->filter_date($value);
  }

  protected function filter_date($value) {
    if($value instanceof SimpleDateTime)
      return $value;
    elseif($value instanceof DateTime)
      return new SimpleDateTime($value);
    else
      return max(0, (int) $value);
  }

  protected function get_default_id() {
    return null;
  }

  protected function get_default_shift() {
    // TODO get default shift
    return null;
  }

  protected function get_default_volunteer() {
    // TODO get default volunteer
    return null;
  }

  protected function get_default_volunteers_count() {
    // TODO get default volunteers count -> needs volunteers manager first
    return 0;
  }

  protected function get_default_is_frozen() {
    return false;
  }

  protected function get_default_created_at() {
    return new SimpleDateTime();
  }

  protected function get_default_updated_at() {
    return new SimpleDateTime();
  }

  protected function get_default_shift_duration() {
    // TODO get default shift duration -> needs shift first
    return 0;
  }

  public function get_db_fields() {
    return array(
      'id' => $this->id,
      'shift_slug' => $this->shift_slug,
      'shift_duration' => $this->shift_duration->seconds(),
      'volunteer_slug' => $this->volunteer_slug,
      'volunteers_count' => $this->volunteers_count,
      'is_frozen' => $this->is_frozen,
      'created_at' => $this->created_at->gmtTimestamp(),
      'updated_at' => $this->updated_at->gmtTimestamp()
    );
  }
}

?>

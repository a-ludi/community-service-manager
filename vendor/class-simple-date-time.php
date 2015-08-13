<?php
/**
 * Extension to DateTime for easy programming. It provides:
 * - an easy interface to get or set the year, month, day, hour and minute separately.
 * - a copy() method
 * - methods for adding/subtracting time intervals (SimpleTimeIntervals)
 */

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

if(! class_exists('SimpleDateTime')) {
  class SimpleDateTime extends DateTime {
    private static $defaultTimezone;

    public function __construct($timeOrDateTime="now", $timezone=null) {
      if(is_null($timezone))
        $timezone = self::defaultTimezone();
      if($timeOrDateTime instanceof DateTime)
        parent::__construct($timeOrDateTime->format('c'));
      else
        parent::__construct($timeOrDateTime, $timezone);
    }

    public static function defaultTimezone($timezone=null) {
      if(is_null(self::$defaultTimezone))
        self::$defaultTimezone = self::getDefaultTimezone($timezone);

      return self::$defaultTimezone;
    }

    private static function getDefaultTimezone($timezone) {
      if(! is_null($timezone) && $timezone instanceof DateTimeZone)
        // The user provides a timezone.
        return $timezone;
      elseif(defined('WPINC') && function_exists('get_option'))
        // Try to get the current timezone from WordPress
        return self::getWordPressTimezone();
      else
        // Fallback to PHP's utitilties.
        return new DateTimeZone(date_default_timezone_get());
    }

    private static function getWordPressTimezone() {
      $timezoneString = get_option('timezone_string');

      // remove old Etc mappings.
      if(empty($timezoneString) || strpos($timezoneString, 'Etc/GMT') !== false)
        $timezoneString = self::getTimezoneStringFromGmtOffset();

      return new DateTimeZone($timezoneString);
    }

    private static function getTimezoneStringFromGmtOffset() {
      $gmtOffset = get_option('gmt_offset') * 3600; // convert hour offset to seconds
      $timezones = DateTimeZone::listAbbreviations();

      foreach($timezones as $timezonesCities)
        foreach($timezonesCities as $cityDetails)
          if($cityDetails['offset'] == $gmtOffset)
            return $cityDetails['timezone_id'];

      // no matching timezone found -- assuming UTC
      return 'UTC';
    }
    
    public function format($format) {
      return parent::format($format);
    }
    
    public function copy() {
      return clone $this;
    }

    public function gmtOffset() {
      $offsetStr = $this->format('O');
      $hours = intval(substr($offsetStr, 0, 3));
      $minutes = intval(substr($offsetStr, 0, 1).substr($offsetStr, 3, 2));

      return $hours + $minutes/60;
    }

    public function gmtTimestamp() {
      $localTimestamp = intval($this->format('U'));
      $gmtOffsetSecs = intval($this->gmtOffset()*3600);

      return $localTimestamp + $gmtOffsetSecs;
    }
    
    public function year($year=null) {
      if(isset($year))
        return $this->setDate($year, $this->month(), $this->day());
      else
        return $this->format('Y') + 0;
    }
    
    public function month($month=null) {
      if(isset($month))
        return $this->setDate($this->year(), $month, $this->day());
      else
        return $this->format('m') + 0;
    }
    
    public function day($day=null) {
      if(isset($day))
        return $this->setDate($this->year(), $this->month(), $day);
      else
        return $this->format('d') + 0;
    }
    
    public function hour($hour=null) {
      if(isset($hour))
        return $this->setTime($hour, $this->minute());
      else
        return $this->format('H') + 0;
    }
    
    public function minute($minute=null) {
      if(isset($minute))
        return $this->setTime($this->hour(), $minute);
      else
        return $this->format('i') + 0;
    }

    public function add($interval) {
      if(! $interval instanceof SimpleTimeInterval)
        self::triggerWrongClassError('SimpleTimeInterval', get_class($interval));

      $this->modify(sprintf('%+d seconds', $interval->seconds()));
      return $this;
    }

    public function subtract($interval) {
      if(! $interval instanceof SimpleTimeInterval)
        self::triggerWrongClassError('SimpleTimeInterval', get_class($interval));

      $this->modify(sprintf('%+d seconds', -$interval->seconds()));
      return $this;
    }

    public function difference($other) {
      $diff = $this->diff($other);
      return new SimpleTimeInterval(array(
        'days' => $diff->days,
        'hours' => $diff->h,
        'minutes' => $diff->i,
        'seconds' => $diff->s
      ));
    }

    private function triggerWrongClassError($expected, $found) {
      trigger_error("Unexpected class '$found' expected '$expected'", E_USER_ERROR);
    }
  }
}
?>
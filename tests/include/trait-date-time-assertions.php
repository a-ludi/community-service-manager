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

trait DateTimeAssertions {
  abstract function assertTrue($bool, $message='%s');

  function assertDateTimeEqual($date_time1, $date_time2, $delta=1, $msg=null){
    $ts1 = (new SimpleDateTime($date_time1))->gmtTimestamp();
    $ts2 = (new SimpleDateTime($date_time2))->gmtTimestamp();
    if(! $delta instanceof SimpleTimeInterval)
      $delta = new SimpleTimeInterval($delta);
    $diff = new SimpleTimeInterval(abs($ts1 - $ts2));

    $test = $diff->seconds() <= $delta->seconds();
    if(is_null($msg) && ! $test) {
      $diff_str = self::build_time_interval_string($diff);
      $delta_str = self::build_time_interval_string($delta);
      $msg = "Timestamps should be equal, but differ by $diff_str >= ".
             "$delta_str seconds.";
    }

    $this->assertTrue($test, $msg);
  }

  private static function build_time_interval_string($time_interval) {
    $str = '';
    foreach($time_interval->toNatural('short') as $unit => $value)
      $str .= " $unit $value";
    // remove leading space
    $str = substr($str, 1);

    return $str;
  }
}
?>
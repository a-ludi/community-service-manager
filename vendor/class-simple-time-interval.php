<?php
/**
 * Handle time intervals with many conversion methods.
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

if(! class_exists('SimpleTimeInterval')) {
  $GLOBALS['SimpleTimeIntervalCanonicalUnits'] = array(
    'seconds' => 's',
    'second' => 's',
    'secs' => 's',
    'sec' => 's',
    's' => 's',
    'minutes' => 'm',
    'minute' => 'm',
    'mins' => 'm',
    'min' => 'm',
    'm' => 'm',
    'hours' => 'h',
    'hour' => 'h',
    'h' => 'h',
    'days' => 'd',
    'day' => 'd',
    'd' => 'd',
    'weeks' => 'w',
    'week' => 'w',
    'w' => 'w',
    'months' => 'M',
    'month' => 'M',
    'M' => 'M',
    'years' => 'y',
    'year' => 'y',
    'y' => 'y'
  );
  $GLOBALS['SimpleTimeIntervalSecondsPer'] = array(
    's' => 1,
    'm' => 60,
    'h' => 3600,
    'd' => 86400,
    'w' => 604800,
    'M' => 2592000,
    'y' => 31536000
  );
  $GLOBALS['SimpleTimeIntervalAllUnits'] = array(
    'short' => array('s', 'm', 'h', 'd', 'w', 'M', 'y'),
    'canonical' => array('s', 'm', 'h', 'd', 'w', 'M', 'y'),
    'abbrev' => array('secs.', 'mins.', 'hours', 'days', 'weeks', 'months', 'years'),
    'long' => array('seconds', 'minutes', 'hours', 'days', 'weeks', 'months', 'years')
  );

  // Translation Helper
  if(false) {
    // short
    _x('s', 'long time unit with unknown count');
    _x('m', 'long time unit with unknown count');
    _x('h', 'long time unit with unknown count');
    _x('d', 'long time unit with unknown count');
    _x('w', 'long time unit with unknown count');
    _x('M', 'long time unit with unknown count');
    _x('y', 'long time unit with unknown count');
    // abbrev
    _x('secs', 'abbrev time unit with unknown count');
    _x('mins', 'abbrev time unit with unknown count');
    _x('hours', 'abbrev time unit with unknown count');
    _x('days', 'abbrev time unit with unknown count');
    _x('weeks', 'abbrev time unit with unknown count');
    _x('months', 'abbrev time unit with unknown count');
    _x('years', 'abbrev time unit with unknown count');
    // long
    _x('seconds', 'long time unit with unknown count');
    _x('minutes', 'long time unit with unknown count');
    _x('hours', 'long time unit with unknown count');
    _x('days', 'long time unit with unknown count');
    _x('weeks', 'long time unit with unknown count');
    _x('months', 'long time unit with unknown count');
    _x('years', 'long time unit with unknown count');

    // short
    _nx('1 s', '%s s', null, 'short time unit');
    _nx('1 m', '%s m', null, 'short time unit');
    _nx('1 h', '%s h', null, 'short time unit');
    _nx('1 d', '%s d', null, 'short time unit');
    _nx('1 w', '%s w', null, 'short time unit');
    _nx('1 M', '%s M', null, 'short time unit');
    _nx('1 y', '%s y', null, 'short time unit');
    // abbrev
    _nx('1 sec.', '%s secs.', null, 'abbrev time unit');
    _nx('1 min.', '%s mins.', null, 'abbrev time unit');
    _nx('1 hour', '%s hours', null, 'abbrev time unit');
    _nx('1 day', '%s days', null, 'abbrev time unit');
    _nx('1 week', '%s weeks', null, 'abbrev time unit');
    _nx('1 month', '%s months', null, 'abbrev time unit');
    _nx('1 year', '%s years', null, 'abbrev time unit');
    // long
    _nx('one second', '%s seconds', null, 'long time unit');
    _nx('one minute', '%s minutes', null, 'long time unit');
    _nx('one hour', '%s hours', null, 'long time unit');
    _nx('one day', '%s days', null, 'long time unit');
    _nx('one week', '%s weeks', null, 'long time unit');
    _nx('one month', '%s months', null, 'long time unit');
    _nx('one year', '%s years', null, 'long time unit');
  }


  class SimpleTimeInterval {
    private $seconds;
    private $mode;

    /**
     * Construct an object from an associative array where the keys are time units or from a 
     * number of seconds. All amounts are added and subsequently rounded to seconds.
     *
     * Valid units are:
     *   * seconds/second/secs/sec/s
     *   * minutes/minute/mins/min/m
     *   * hours/hour/h
     *   * days/day/d
     *   * weeks/week/w
     *   * months/month/M (= 30 days)
     *   * years/year/y (= 365 days)
     */
    public function __construct($timeArrayOrSeconds, $mode='round') {
      if(is_array($timeArrayOrSeconds)) {
        $this->seconds = 0;
        foreach ($timeArrayOrSeconds as $unit => $value)
          $this->seconds += self::toSeconds($value, $unit);
        $this->seconds = (int) round($this->seconds);
      } else {
        $this->seconds = (int) round($timeArrayOrSeconds);
      }
      $this->mode($mode);
    }

    protected static function toSeconds($value, $unit) {
      return $value * self::secondsPer($unit);
    }

    public static function secondsPer($unit) {
      global $SimpleTimeIntervalSecondsPer;
      return $SimpleTimeIntervalSecondsPer[self::canonicalUnit($unit)];
    }

    protected static function canonicalUnit($unit) {
      global $SimpleTimeIntervalCanonicalUnits;
      return $SimpleTimeIntervalCanonicalUnits[$unit];
    }

    protected static function unitIndex($unit) {
      return array_search(self::canonicalUnit($unit), self::units('canonical', false));
    }

    public static function unitValid($unit) {
      return ! is_null(self::canonicalUnit($unit));
    }

    protected static function sortUnits(&$units, $desc=false) {
      $unitIndex = array_flip(array('s', 'm', 'h', 'd', 'w', 'M', 'y'));

      return usort($units, function($a, $b) use ($unitIndex, $desc) {
        $aC = self::canonicalUnit($a);
        $bC = self::canonicalUnit($b);
        return ($desc ? -1 : 1) * ($unitIndex[$aC] - $unitIndex[$bC]);
      });
    }

    /**
     * This controls or returns the 'rounding mode', avaiable modes are 'floor', 'round', 'ceil',
     * 'rest', 'float'. If 'rest' is chosen the remaining seconds will be stored under the key 'rest'.
     */
    public function mode($mode=null) {
      if(is_null($mode)) {
        return $this->mode;
      } else if(in_array($mode, array('floor', 'round', 'ceil', 'rest', 'float'))) {
        $this->mode = $mode;
        return true;
      } else {
        return false;
      }
    }

    /**
     * Returns an associative array representing this time interval in terms of the units given as
     * arguments. Takes either an arbitrary number of string arguments or a single array of strings.
     */
    public function to() {
      if(func_num_args() == 0)
        return null;

      if(func_num_args() == 1 && is_array(func_get_arg(0)))
        $units = func_get_arg(0);
      else
        $units = func_get_args();
      $units = array_filter($units, array('SimpleTimeInterval', 'unitValid'));
      self::sortUnits($units, true);

      $seconds = $this->seconds;
      $result = array();
      foreach($units as $unit) {
        $result[$unit] = floor($seconds / self::secondsPer($unit));
        $seconds -= $result[$unit] * self::secondsPer($unit);
      }

      $leastUnit = array_pop($units);
      $roundingResult = $this->round($result[$leastUnit], $leastUnit, $seconds);

      return array_merge($result, $roundingResult);
    }

    /**
     * Returns an associative array representing this time interval with a natural choice of units.
     * If this time interval has zero-length the unit $nullUnit will be formatted and set to zero.
     */
    public function toNatural($format='long', $nullUnit='s') {
      $timeArray = $this->to(array_reverse(self::units('canonical')));
      $resultArray = array();
      foreach($timeArray as $unit => $value)
        if($value > 0)
          $resultArray[self::formatUnit($unit, $format)] = $value;

      if(empty($resultArray))
        $resultArray[self::formatUnit($nullUnit, $format)] = 0;

      return $resultArray;
    }

    /**
     * Returns an integer or float representing this time interval in terms of the unit given as
     * argument.
     *
     * Note: the mode 'rest' works just like 'floor'. If this mode is desired use the method to().
     */
    public function toSingle($unit) {
      if(! self::unitValid($unit))
        return null;

      $seconds = $this->seconds;
      $result = floor($seconds / self::secondsPer($unit));
      $seconds -= $result * self::secondsPer($unit);
      $roundingResult = $this->round($result, $unit, $seconds);

      return $roundingResult[$unit];
    }

    private function round($value, $unit, $seconds) {
      $result = array();
      $result[$unit] = $value;
      switch($this->mode) {
        case 'floor':
          break;
        case 'ceil':
          if($seconds > 0)
            $result[$unit]++;
          break;
        case 'rest':
          $result['rest'] = $seconds;
          break;
        case 'round':
          if(2 * $seconds >= self::secondsPer($unit))
            $result[$unit]++;
          break;
        case 'float':
          $result[$unit] += (float) $seconds / (float) self::secondsPer($unit);
          break;
        default:
          trigger_error("Unkown rounding mode '$this->mode'", E_USER_NOTICE);
          break;
      }
      return $result;
    }

    public static function units($format='long') {
      global $SimpleTimeIntervalAllUnits;
      if(isset($SimpleTimeIntervalAllUnits[$format])) {
        return $SimpleTimeIntervalAllUnits[$format];
      } else {
        trigger_error("Unkown format specifier '$format'", E_USER_NOTICE);
        return null;
      }
    }

    /**
     * Return the unit in the given format.
     */
    public static function formatUnit($unit, $format='long', $plural_or_singular='plural') {
      $units = self::units($format);
      $unit = $units[array_search(self::canonicalUnit($unit), self::units('canonical'))];

      switch($plural_or_singular) {
        case 'plural':
          return $unit;
        case 'singular':
          return self::singularize($unit);
        default:
          trigger_error("Third parameter must be either 'plural' or 'singular'", E_USER_NOTICE);
          return null;
      }
    }

    public static function translate($unit, $format='long', $count='unkown', $textDomain=null) {
      if(! (function_exists('_nx') && function_exists('_x'))) {
        trigger_error("Method not avaiable because functions '_nx' and/or '_x' are missing", E_USER_NOTICE);
        return null;
      }

      if('unknown' == $count) {
        $string = self::formatUnit($unit, $format);
        $comment = "$format time unit with unknown count";
        
        if(is_null($textDomain))
          return _x($string, $comment);
        else
          return _x($string, $comment, $textDomain);
      } else {
        $singularUnit = self::formatUnit($unit, $format, 'singular');
        $pluralUnit = self::formatUnit($unit, $format, 'plural');
        $singularString = $format == 'long' ? "one $singularUnit" : "1 $singularUnit";
        $pluralString = "%s $pluralUnit";
        $comment = "$format time unit";

        if(is_null($textDomain))
          return _nx($singularString, $pluralString, $count, $comment);
        else
          return _nx($singularString, $pluralString, $count, $comment, $textDomain);
      }
    }

    public static function singularize($unit) {
      if(1 === strlen($unit))
        return $unit;
      else
        // Strip the trailing 's';
        return preg_replace('/s(\.?)$/', '\1', $unit);
    }

    /**
     * Return the greatest unit which represents the current interval exactly in the given format.
     */
    public function greatestExactUnit($format='long') {
      foreach (array_reverse(self::units('canonical')) as $unit)
        if($this->seconds % self::secondsPer($unit) == 0)
          return self::formatUnit($unit, $format);

      trigger_error(
        'Statement should be unreachable. Please contact the author of this file.',
        E_USER_ERROR
      );
    }

    public function seconds($seconds=null) {
      if(is_null($seconds))
        return $this->seconds;
      else
        $this->seconds = (int) round($seconds);
    }
  }

  if(php_sapi_name() == 'cli') {
    $unitTests = array();
    
    $unitTests['Interval to Seconds'] = function() {
      $expected = array(
        array('s' => 18271),
        array('s' => 18271),
        array('s' => 18271),
        array('s' => 18271, 'rest' => 0),
        array('s' => 18271.0)
      );
      $tiFloor = new SimpleTimeInterval(array('h' => 5, 'm' => 4, 's' => 31), 'floor');
      $tiRound = new SimpleTimeInterval(array('h' => 5, 'm' => 4, 's' => 31), 'round');
      $tiCeil = new SimpleTimeInterval(array('h' => 5, 'm' => 4, 's' => 31), 'ceil');
      $tiRest = new SimpleTimeInterval(array('h' => 5, 'm' => 4, 's' => 31), 'rest');
      $tiFloat = new SimpleTimeInterval(array('h' => 5, 'm' => 4, 's' => 31), 'float');
      $results = array(
        $tiFloor->to('s'),
        $tiRound->to('s'),
        $tiCeil->to('s'),
        $tiRest->to('s'),
        $tiFloat->to('s')
      );
      
      return $results == $expected;
    };

    $unitTests['Modes'] = function() {
      $expected = array(
        array('m' => 500),
        array('m' => 501),
        array('m' => 501),
        array('m' => 500, 'rest' => 31),
        array('m' => 500 + 31.0/60.0)
      );
      $tiFloor = new SimpleTimeInterval(array('m' => 500, 's' => 31), 'floor');
      $tiRound = new SimpleTimeInterval(array('m' => 500, 's' => 31), 'round');
      $tiCeil = new SimpleTimeInterval(array('m' => 500, 's' => 31), 'ceil');
      $tiRest = new SimpleTimeInterval(array('m' => 500, 's' => 31), 'rest');
      $tiFloat = new SimpleTimeInterval(array('m' => 500, 's' => 31), 'float');
      $results = array(
        $tiFloor->to('m'),
        $tiRound->to('m'),
        $tiCeil->to('m'),
        $tiRest->to('m'),
        $tiFloat->to('m')
      );
      
      return $results == $expected;
    };
    
    $unitTests['Preserve Input Units'] = function() {
      $expected = array('y' => 0, 'months' => 0, 'week' => 0, 'seconds' => 0);
      $ti = new SimpleTimeInterval(0);
      $results = $ti->to('y', 'months', 'week', 'seconds');
      
      return $results == $expected;
    };
    
    $unitTests['Interval toSingle Seconds'] = function() {
      $expected = array(
        18271,
        18271,
        18271,
        18271,
        18271.0
      );
      $tiFloor = new SimpleTimeInterval(array('h' => 5, 'm' => 4, 's' => 31), 'floor');
      $tiRound = new SimpleTimeInterval(array('h' => 5, 'm' => 4, 's' => 31), 'round');
      $tiCeil = new SimpleTimeInterval(array('h' => 5, 'm' => 4, 's' => 31), 'ceil');
      $tiRest = new SimpleTimeInterval(array('h' => 5, 'm' => 4, 's' => 31), 'rest');
      $tiFloat = new SimpleTimeInterval(array('h' => 5, 'm' => 4, 's' => 31), 'float');
      $results = array(
        $tiFloor->toSingle('s'),
        $tiRound->toSingle('s'),
        $tiCeil->toSingle('s'),
        $tiRest->toSingle('s'),
        $tiFloat->toSingle('s')
      );
      
      return $results == $expected;
    };
    
    $unitTests['Interval toNatural'] = function() {
      $expected = array(
        array('hours' => 5, 'minutes' => 4, 'seconds' => 1),
        array('days' => 1, 'minutes' => 4, 'seconds' => 31),
        array('seconds' => 0),
        array('hours' => 0)
      );
      $ti1 = new SimpleTimeInterval(array('h' => 5, 'm' => 4, 's' => 1));
      $ti2 = new SimpleTimeInterval(array('h' => 24, 'm' => 4, 's' => 31));
      $ti3 = new SimpleTimeInterval(0);
      $ti4 = new SimpleTimeInterval(0);
      $results = array(
        $ti1->toNatural(),
        $ti2->toNatural(),
        $ti3->toNatural(),
        $ti4->toNatural('long', 'h')
      );
      
      return $results == $expected;
    };
    
    $unitTests['Greatest Exact Unit'] = function() {
      $expected = array('s', 'm', 'h', 'd', 'w', 'M', 'y', 'days', 'years', 'years');
      $tiS = new SimpleTimeInterval(array('s' => 5));
      $tiMin = new SimpleTimeInterval(array('m' => 5));
      $tiH = new SimpleTimeInterval(array('h' => 5));
      $tiD = new SimpleTimeInterval(array('d' => 5));
      $tiW = new SimpleTimeInterval(array('w' => 5));
      $tiM = new SimpleTimeInterval(array('M' => 5));
      $tiY = new SimpleTimeInterval(array('y' => 5));
      $tiCase1 = new SimpleTimeInterval(259200);
      $tiCase2 = new SimpleTimeInterval(array('y' => 1));
      $tiCase3 = new SimpleTimeInterval(array('y' => 5));
      $results = array(
        $tiS->greatestExactUnit('short'),
        $tiMin->greatestExactUnit('short'),
        $tiH->greatestExactUnit('short'),
        $tiD->greatestExactUnit('short'),
        $tiW->greatestExactUnit('short'),
        $tiM->greatestExactUnit('short'),
        $tiY->greatestExactUnit('short'),
        $tiCase1->greatestExactUnit('long'),
        $tiCase2->greatestExactUnit('long'),
        $tiCase3->greatestExactUnit('long')
      );

      return $results == $expected;
    };
    
    $unitTests['All Units in All Formats Present'] = function() {
      $expected = array(
        'short' => array('s', 'm', 'h', 'd', 'w', 'M', 'y'),
        'abbrev' => array('secs.', 'mins.', 'hours', 'days', 'weeks', 'months', 'years'),
        'long' => array('seconds', 'minutes', 'hours', 'days', 'weeks', 'months', 'years')
      );

      foreach(array('short', 'abbrev', 'long') as $format)
        if(SimpleTimeInterval::units($format) != $expected[$format])
          return false;
      
      return true;
    };

    $unitTests['Singularize Units'] = function () {
      $expected = array(
        'short' => array('s', 'm', 'h', 'd', 'w', 'M', 'y'),
        'abbrev' => array('sec.', 'min.', 'hour', 'day', 'week', 'month', 'year'),
        'long' => array('second', 'minute', 'hour', 'day', 'week', 'month', 'year')
      );

      foreach(array('short', 'abbrev', 'long') as $format) {
        $singular_units = array_map(array('SimpleTimeInterval', 'singularize'), SimpleTimeInterval::units($format));
        if($singular_units != $expected[$format])
          return false;
      }
      
      return true;
    };

    $passedCount = 0;
    foreach($unitTests as $name => $test) {
      $testResult = call_user_func($test);
      echo $name . ' Test ... ' . ($testResult ? 'passed' : 'failed') . PHP_EOL;
      if($testResult)
         $passedCount++;
    }

    echo '---' . PHP_EOL;
    echo 'Passed ' . $passedCount . ' of ' . count($unitTests) . ' tests.' . PHP_EOL;
  }
}
?>
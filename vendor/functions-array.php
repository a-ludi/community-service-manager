<?php
/**
 * Provides some additional functions for array handling.
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

  if(! function_exists('array_get')) {
    /**
     * Get the entry of an array or a default value if it is missing. Never
     * issues any errors.
     */
    function array_get(&$array, $key, $default = null) {
      return isset($array[$key]) ? $array[$key] : $default;
    }
  }

  if(! function_exists('array_peek')) {
    /**
     * Retrieve the last entry of array or a default value. This works only
     * for numerically indexed arrays. Never issues any errors.
     */
    function array_peek(&$array, $default = null) {
      $key = count($array) - 1;
      return isset($array[$key]) ? $array[$key] : $default;
    }
  }

  if(! function_exists('array_is_assoc')) {
    /**
     * Checks whethter an array is associative (contains string keys) or not.
     */
    // Thanks to Captain kurO from StackOverflow
    // http://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential/4254008#4254008
    function array_is_assoc($array) {
      return 0 !== count(array_filter(array_keys($array), 'is_string'));
    }
  }

  if(! function_exists('array_select')) {
    /**
     * Selects values from $array with the given keys. The keys may be given as either a
     * non-associative array, the remaining arguments of this function or an associative array.
     * In the last case the keys of the associative array will be used for comparision but only
     * after filtering out the values which evaluate to false.
     * 
     * __Example 1:__
     *     <?php
     *     $array = array('foo' => 'bar', 'ernie' => 'bert', 'answer' => 42);
     *
     *     var_dump(array_select($array, 'foo', 'answer'));
     *     var_dump(array_select($array, array('foo', 'answer')));
     *     ?>
     *
     * The above example will output:
     *     array(2) {
     *       'foo' =>
     *       string(3) "bar"
     *       'answer' =>
     *       int(42)
     *     }
     *     array(2) {
     *       'foo' =>
     *       string(3) "bar"
     *       'answer' =>
     *       int(42)
     *     }
     *
     * __Example 2:__
     *     <?php
     *     $array = array('foo' => 'bar', 'ernie' => 'bert', 'answer' => 42);
     *
     *     var_dump(array_select($array, array('foo' => true, 'ernie' => null, 'answer' => 42)));
     *     ?>
     *
     * The above example will output:
     *     array(2) {
     *       'foo' =>
     *       string(3) "bar"
     *       'answer' =>
     *       int(42)
     *     }
     *     array(2) {
     *       'foo' =>
     *       string(3) "bar"
     *       'answer' =>
     *       int(42)
     *     }
     */

    function array_select($array, $keys) {
      if(! is_array($keys)) {
        $keys = func_get_args();
        array_shift($keys);
      }
      if(array_is_assoc($keys))
        $keys = array_filter($keys);
      else
        $keys = array_flip($keys);

      return array_intersect_key($array, $keys);
    }
  }

  if(! function_exists('array_all')) {
    /**
     * Returns true if all elements of the array evaluate to true.
     */
    function array_all($array) {
      return array_reduce($array, 'fn20658_and', true);
    }
    function fn20658_and($a, $b) { return $a && $b; }
  }

  if(! function_exists('array_any')) {
    /**
     * Returns true if any elements of the array evaluate to true.
     */
    function array_any($array) {
      return array_reduce($array, 'fn20658_or', false);
    }
    function fn20658_or($a, $b) { return $a || $b; }
  }
?>
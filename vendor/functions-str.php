<?php
/**
 * Provides some additional functions for HTML manipulation.
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

  if(! function_exists('str_dashed_to_camel')) {
    /**
     * Convert a name from dashed to camel-cased.
     */
    function str_dashed_to_camel($dashedName, $upperCase=false) {
      $nameParts = explode('-', $dashedName);
      $camelCasedName = $upperCase ? ucfirst(strtolower($nameParts[0])) : strtolower($nameParts[0]);
      for($i = 1; $i < count($nameParts); $i++)
        $camelCasedName .= ucfirst(strtolower($nameParts[$i]));

      return $camelCasedName;
    }
  }

  if(! function_exists('str_camel_to_dashed')) {
    /**
     * Convert a name from camel-cased to dashed.
     */
    function str_camel_to_dashed($camel_case, $upper_case='auto') {
      $name_parts = null;
      preg_match_all('/^[a-z]+|[A-Z][a-z]*|[0-9]+/', $camel_case, $name_parts);
      if(0 === count($name_parts[0]))
        return '';

      $fst_char = $name_parts[0][0][0];
      $name_parts = array_map('strtolower', $name_parts[0]);
      if(('auto' === $upper_case && strtoupper($fst_char) === $fst_char) ||
         ('auto' !== $upper_case && $upper_case))
        $name_parts = array_map('ucfirst', $name_parts);


      return join('-', $name_parts);
    }
  }

  if(! function_exists('str_dashed_to_underscore')) {
    /**
     * Convert a name from dashed to underscore.
     */
    function str_dashed_to_underscore($dashedName) {
      return str_replace('-', '_', $dashedName);
    }
  }

  if(! function_exists('str_squeeze')) {
    /**
     * Squeeze whitespace in a string replacing it by spaces only. The string
     * is stripped from leading and trailing whitespace as well.
     */
    function str_squeeze($str) {
      return trim(preg_filter('/\s+/m', ' ', $str));
    }
  }

  if(! function_exists('str_reindent')) {
    /**
     * Remake the indentation of a string. The indentation of the first line
     * will be removed from the other the lines, e.g.:
     *
     *     echo str_reindent("
     *       SELECT
     *         id,
     *         login,
     *         password_hash,
     *       FROM
     *         users
     *       WHERE
     *         login = 'foobaz';
     *     ");
     *
     * This will yield:
     *
     *     SELECT
     *       id,
     *       login,
     *       password_hash,
     *     FROM
     *       users
     *     WHERE
     *       login = 'foobaz';
     */
    function str_reindent($str) {
      $matches = array();
      if(false === preg_match('/^\n\s+/m', $str, $matches))
        return $str;

      return trim(str_replace($matches[0], "\n", $str));
    }
  }
?>
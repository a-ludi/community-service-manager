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
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

  if(! function_exists('html_remove_classes')) {
    /**
     * Removes the given HTML classes from the given string.
     */
    function html_remove_classes($classString) {
      $classes = func_get_args();
      array_shift($classes);
      $classString = str_ireplace($classes, '', $classString);

      return preg_replace(
        array('/\s+/', '/^\s+/', '/\s+$/'),
        array(' ', '', ''),
        $classString
      );
    }
  }

  if(! function_exists('html_text_color')) {
    /**
     * Returns a suitable text color for the given background color.
     */
    // Code adapted from http://serennu.com/colour/rgbtohsl.php
    function html_text_color($bgHexcode) {
      $redhex  = substr($bgHexcode, 1, 2);
      $greenhex = substr($bgHexcode, 3, 2);
      $bluehex = substr($bgHexcode, 5, 2);

      // $var_r, $var_g and $var_b are the three decimal fractions to be input to our RGB-to-HSL
      // conversion routine
      $var_r = hexdec($redhex)/255;
      $var_g = hexdec($greenhex)/255;
      $var_b = hexdec($bluehex)/255;

      $var_min = min($var_r,$var_g,$var_b);
      $var_max = max($var_r,$var_g,$var_b);

      $l = ($var_max + $var_min) / 2;

      if($l < 0.5) {
        return 'white';
      } else {
        return 'black';
      }
    }
  }
?>
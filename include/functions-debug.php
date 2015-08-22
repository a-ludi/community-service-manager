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

if(defined('WP_DEBUG_LOG') && true === WP_DEBUG_LOG) {
  if(! defined('CSM_LOG_FILE') || empty(CSM_LOG_FILE))
    define('CSM_LOG_FILE', 'php://stderr');

  function csm_log($message) {
    error_log(
      date('[Y-m-d H:i:s] ').$message.PHP_EOL,
      3, // use output file
      CSM_LOG_FILE);
  }
} else {
  function csm_log($message) {}
}

if(defined('WP_DEBUG') && true === WP_DEBUG) {
  function csm_dbg_print($string, $opts=array()) {
    list($strings, $opts) = csm_dbg_extract_options(func_get_args(), array(
      'sep' => ' ',
      'end' => PHP_EOL
    ));
    
    echo join($opts['sep'], $strings).$opts['end'];
  }
} else {
  function csm_dbg_print($string, $opts=array()) { func_get_args(); }
}

if(defined('WP_DEBUG') && true === WP_DEBUG) {
  function csm_dbg_dump($vars) {
    call_user_func_array('var_dump', func_get_args());
  }
} else {
  function csm_dbg_dump($vars) { func_get_args(); }
}

if(defined('WP_DEBUG') && true === WP_DEBUG) {
  function csm_dbg_extract_options($args, $default_opts) {
    $last_arg = $args[count($args) - 1];
    $opts = is_array($last_arg) ? $last_arg : array();
    if(is_array($last_arg))
      unset($args[count($args) - 1]);
    $opts = array_merge($default_opts, $opts);

    return array($args, $opts);
  }
}
?>

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

abstract class CSM_AbstractDBManager {
  public function __construct() {
    assert(false, 'Call to unimplemented Method CSM_AbstractDbManager::__construct().'); // TODO
  }

  /**
   * Returns the version of the current database schema.
   */
  public function db_version() {
    assert(false, 'Call to unimplemented Method CSM_AbstractDbManager::db_version().'); // TODO
  }

  /**
   * Runs all pending migrations. The migrations will be run in order of
   * definition and the database version is increased for every successful
   * migration. If a migration fails the database version will not be
   * increased and a boolean false will be returned.
   *
   * __Migration:__ A migration is a method beginning with 'migrate'. It takes
   * no arguments and returns a boolean to indicate the success status. A
   * migration is pending iff. its position (beginning with `1`) is greater
   * than the current database version.
   */
  public function migrate() {
    assert(false, 'Call to unimplemented Method CSM_AbstractDbManager::migrate().'); // TODO
  }

  public function purge_db() {
    assert(false, 'Call to unimplemented Method CSM_AbstractDbManager::migrate().'); // TODO
  }
}
?>

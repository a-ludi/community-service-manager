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

class CSM_UnitTests extends CSM_TestSuite {
  function __construct() {
    parent::__construct();
    $this->addFile('unit/test-journal-entry.php');
    $this->addFile('unit/test-journal.php');
    $this->addFile('unit/test-abstract-db-manager.php');
    $this->addFile('unit/test-db-manager.php');
    $this->addFile('unit/test-person.php');
  }
}
?>

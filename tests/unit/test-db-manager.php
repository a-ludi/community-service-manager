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

class TestDBManager extends CSM_UnitTestCase {
  function __construct() {
    parent::__construct(array(
      // TODO place fixture names here
    ));
  }

  function setUp() {
    parent::setUp();
    // TODO need setup?
  }

  function test_the_answer_is_fourtytwo() {
    // TODO write your own tests
    $this->assertTrue(42);
  }
}
?>

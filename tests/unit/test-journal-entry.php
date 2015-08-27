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

class TestJournalEntry extends CSM_UnitTestCase {
  use DateTimeAssertions;

  function __construct() {
    parent::__construct(array());
  }

  function test_journal_entry_exists() {
    $this->assertTrue(
      class_exists('CSM_JournalEntry'),
      'Class CSM_JournalEntry should exist'
    );
  }

  function test_default_value_for_id() {
    $journal_entry = new CSM_JournalEntry();
    $this->assertEqual($journal_entry->id, null);
  }

  function test_default_value_for_shift() {
    $journal_entry = new CSM_JournalEntry();
    $this->assertEqual($journal_entry->shift, null);

    $journal_entry = new CSM_JournalEntry(array('shift_slug' => 'shift1'));
    $this->assertIsA($journal_entry->shift, 'CSM_Shift') and
      $this->assertEqual($journal_entry->shift->slug, 'shift1');
  }

  function test_default_value_for_volunteer() {
    $journal_entry = new CSM_JournalEntry();
    $this->assertEqual($journal_entry->volunteer, null);

    $journal_entry = new CSM_JournalEntry(array('volunteer_slug' => 'volunteer1'));
    $this->assertIsA($journal_entry->volunteer, 'CSM_Volunteer') and
      $this->assertEqual($journal_entry->volunteer->slug, 'volunteer1');
  }

  function test_default_value_for_volunteers_count() {
    $journal_entry = new CSM_JournalEntry();
    // TODO needs 3 volunteers in the fixtures
    $this->assertEqual($journal_entry->volunteers_count, 3);
  }

  function test_default_value_for_is_frozen() {
    $journal_entry = new CSM_JournalEntry();
    $this->assertEqual($journal_entry->is_frozen, false);
  }

  function test_default_value_for_created_at() {
    $journal_entry = new CSM_JournalEntry();
    $now = new SimpleDateTime();
    $this->assertDateTimeEqual($journal_entry->created_at, $now);
  }

  function test_default_value_for_updated_at() {
    $journal_entry = new CSM_JournalEntry();
    $now = new SimpleDateTime();
    $this->assertDateTimeEqual($journal_entry->created_at, $now);
  }

  function test_default_value_for_shift_duration() {
    $journal_entry = new CSM_JournalEntry();
    $this->assertEqual($journal_entry->shift_duration, null);

    $journal_entry = new CSM_JournalEntry(array('shift_slug' => 'shift1'));
    $this->assertIsA($journal_entry->shift, 'SimpleTimeInterval') and
      $this->assertEqual($journal_entry->shift->seconds(), 1*SECS_PER_HOUR);
  }

  function test_set_and_get_id() {
    $journal_entry = new CSM_JournalEntry();

    $journal_entry->id = null;
    $this->assertEqual($journal_entry->id, null);

    $journal_entry->id = 1;
    $this->assertEqual($journal_entry->id, 1);

    $journal_entry->id = '1';
    $this->assertEqual($journal_entry->id, 1);
  }

  function test_set_and_get_shift_slug() {
    $journal_entry = new CSM_JournalEntry();

    $journal_entry->shift_slug = 'shift_slug';
    $this->assertEqual($journal_entry->shift_slug, 'shift_slug');
  }

  function test_shift_gets_updated_on_shift_slug_change() {
    $journal_entry = new CSM_JournalEntry();

    $journal_entry->shift_slug = 'shift1';
    $shift1 = $journal_entry->shift;
    $journal_entry->shift_slug = 'shift2';
    $shift2 = $journal_entry->shift;

    $this->assertNotNull($shift1) and $this->assertNotNull($shift2) and
      $this->assertNotEqual($shift1, $shift2);
  }

  function test_set_and_get_shift_duration() {
    $journal_entry = new CSM_JournalEntry();

    $journal_entry->shift_duration = 10;
    $this->assertIsA($journal_entry->shift_duration, 'SimpleTimeInterval') and
      $this->assertEqual($journal_entry->shift_duration->seconds(), 10);

    $ti = new SimpleTimeInterval(42);
    $journal_entry->shift_duration = $ti;
    $this->assertEqual($journal_entry->shift_duration, $ti);
  }

  function test_shift_duration_must_be_positive() {
    $journal_entry = new CSM_JournalEntry();

    $journal_entry->shift_duration = 10;
    $old_shift_duration = $journal_entry->shift_duration;
    $this->expectError();
    $journal_entry->shift_duration = -10;
    $this->assertEqual($journal_entry->shift_duration, $old_shift_duration);
  }

  function test_set_and_get_volunteer_slug() {
    $journal_entry = new CSM_JournalEntry();

    $journal_entry->volunteer_slug = 'volunteer_slug';
    $this->assertEqual($journal_entry->volunteer_slug, 'volunteer_slug');
  }

  function test_volunteer_gets_updated_on_volunteer_slug_change() {
    $journal_entry = new CSM_JournalEntry();

    $journal_entry->volunteer_slug = 'volunteer1';
    $volunteer1 = $journal_entry->volunteer;
    $journal_entry->volunteer_slug = 'volunteer2';
    $volunteer2 = $journal_entry->volunteer;

    $this->assertNotNull($volunteer1) and $this->assertNotNull($volunteer2) and
      $this->assertNotEqual($volunteer1, $volunteer2);
  }

  function test_set_and_get_volunteers_count() {
    $journal_entry = new CSM_JournalEntry();

    $journal_entry->volunteers_count = 42;
    $this->assertEqual($journal_entry->volunteers_count, 42);
  }

  function test_volunteers_count_must_be_non_negative() {
    $journal_entry = new CSM_JournalEntry();

    $journal_entry->volunteers_count = 42;
    $old_volunteers_count = $journal_entry->volunteers_count;
    $this->expectError();
    $journal_entry->volunteers_count = -1;
    $this->assertEqual($journal_entry->volunteers_count, $old_volunteers_count);
  }

  function test_set_and_get_is_frozen() {
    $journal_entry = new CSM_JournalEntry();

    $journal_entry->is_frozen = true;
    $this->assertTrue($journal_entry->is_frozen);

    $journal_entry->is_frozen = false;
    $this->assertFalse($journal_entry->is_frozen);
  }

  function test_set_and_get_created_at_with_date_time() {
    $journal_entry = new CSM_JournalEntry();

    $date_time = new DateTime();
    $journal_entry->created_at = $date_time;
    $this->assertDateTimeEqual($journal_entry->created_at, $date_time);
  }

  function test_set_and_get_created_at_with_simple_date_time() {
    $journal_entry = new CSM_JournalEntry();

    $date_time = new SimpleDateTime();
    $journal_entry->created_at = $date_time;
    $this->assertDateTimeEqual($journal_entry->created_at, $date_time);
  }

  function test_set_created_at_with_non_date_time_triggers_error() {
    $journal_entry = new CSM_JournalEntry();

    $non_date_time = '2001-05-25';
    $this->expectError(new PatternExpectation('/created_at/'));
    $journal_entry->created_at = $non_date_time;
  }

  function test_set_and_get_updated_at_with_date_time() {
    $journal_entry = new CSM_JournalEntry();

    $date_time = new DateTime();
    $journal_entry->updated_at = $date_time;
    $this->assertDateTimeEqual($journal_entry->updated_at, $date_time);
  }

  function test_set_and_get_updated_at_with_simple_date_time() {
    $journal_entry = new CSM_JournalEntry();

    $date_time = new SimpleDateTime();
    $journal_entry->updated_at = $date_time;
    $this->assertDateTimeEqual($journal_entry->updated_at, $date_time);
  }

  function test_set_updated_at_with_non_date_time_triggers_error() {
    $journal_entry = new CSM_JournalEntry();

    $non_date_time = '2001-05-25';
    $this->expectError(new PatternExpectation('/updated_at/'));
    $journal_entry->updated_at = $non_date_time;
  }
}
?>

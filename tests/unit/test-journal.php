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

Mock::generate('CSM_JournalEntry', 'CSM_MockJournalEntry');
class TestJournal extends CSM_UnitTestCase {
  function __construct() {
    parent::__construct(array(
      'wp_csm_journal'
    ));
  }

  function setUp() {
    parent::setUp();
    $this->journal = CSM_Journal::get_instance();
  }

  function test_journal_exists() {
    $this->assertTrue(
      class_exists('CSM_Journal'),
      'Class CSM_Journal should exist'
    );
  }

  function test_get_instance() {
    $this->assertNotNull($this->journal);
    $this->assertIsA($this->journal, 'CSM_Journal');
    $this->assertIdentical($this->journal, CSM_Journal::get_instance());
  }

  function test_table_name() {
    $this->assertEqual($this->journal->table_name(), 'wp_csm_journal');
  }

  function test_db_version() {
    $this->assertEqual($this->journal->get_db_version(), 1);
  }

  function test_migrate() {
    $this->dbh->query('DROP TABLE IF EXISTS wp_csm_journal');
    update_option('csm_db_version', 0);
    $this->journal->migrate();

    $num_tables = $this->dbh->
      query('SHOW TABLES LIKE "wp_csm_journal"')->
      rowCount();
    $this->assertEqual($num_tables, 1);

    $columns = $this->dbh->
      query('SHOW COLUMNS FROM wp_csm_journal')->
      fetchAll(PDO::FETCH_COLUMN, 0);
    $columns = sort($columns);
    $expected_columns = array('created_at', 'id', 'is_frozen',
      'shift_duration', 'shift_slug', 'updated_at', 'volunteer_slug',
      'volunteers_count');
    $this->assertEqual($columns, $expected_columns);

    $this->assertEqual($this->journal->get_db_version(), 1);
  }

  function test_db_clear() {
    $this->journal->db_clear();

    $num_tables = $this->dbh->
      query('SHOW TABLES LIKE "wp_csm_journal"')->
      rowCount();
    $this->assertEqual($num_tables, 0);

    $this->journal->migrate();
  }

  function test_find_all_without_constraints() {
    $entries = $this->journal->find_all();

    $this->assertIsA($entries, 'array');
    $this->assertCount($entries, 4);
    foreach($entries as $entry)
      $this->assertIsA($entry, 'CSM_JournalEntry');
  }

  function test_find_all_with_constraints() {
    $entries = $this->journal->find_all(array(
      'volunteer_slug' => 'volunteer2'
    ));

    $this->assertIsA($entries, 'array');
    $this->assertCount($entries, 3);
    foreach($entries as $entry)
      $this->assertIsA($entry, 'CSM_JournalEntry');
  }

  function test_find() {
    $entry = $this->journal->find(
      $this->auto_id('journal_entry1')
    );

    $this->assertIsA($entry, 'CSM_JournalEntry') and
      $this->assertEqual($entry->slug, 'journal_entry1');
  }

  function test_commit_new_entry() {
    $entry_data = array(
      'id' => null,
      'shift_slug' => 'shift42',
      'shift_duration' => 42,
      'volunteer_slug' => 'volunteer42',
      'volunteers_count' => 42,
      'is_frozen' => false,
      'created_at' => $this->env('now'),
      'updated_at' => $this->env('now')
    );
    $mock_entry = new CSM_MockJournalEntry();
    $mock_entry->returns('get_db_fields', $entry_data);
    $mock_entry->expectAtLeastOnce;

    $this->journal->commit($mock_entry);

    $row = $this->dbh->
      query('SELECT * FROM wp_csm_journal WHERE shift_slug = "shift42"')->
      fetch(PDO::FETCH_ASSOC);
    if($this->assertTrue($row)) {
      $row['id'] = null;
      $this->assertEqual($row, $entry_data);
    }
  }

  function test_commit_updated_entry() {
    $entry_data = array(
      'id' => $this->auto_id('journal_entry1'),
      'shift_slug' => 'shift42',
      'shift_duration' => 42,
      'volunteer_slug' => 'volunteer42',
      'volunteers_count' => 42,
      'is_frozen' => false,
      'created_at' => $this->env('now'),
      'updated_at' => $this->env('now')
    );
    $mock_entry = new CSM_MockJournalEntry();
    $mock_entry->returns('get_db_fields', $entry_data);
    $mock_entry->expectAtLeastOnce('get_db_fields');

    $this->journal->commit($mock_entry);

    $row = $this->dbh->
      query('SELECT * FROM wp_csm_journal WHERE id = '.$this->auto_id('journal_entry1'))->
      fetch(PDO::FETCH_ASSOC);

    $this->assertEqual($row, $entry_data);
  }
}
?>

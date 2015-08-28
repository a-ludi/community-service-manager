<?php
/* Copyright © 2014 Arne Ludwig <arne.ludwig@posteo.de>
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

class CSM_Journal {
  protected $table_columns = array(
    'id',
    'shift_slug',
    'volunteer_slug',
    'shift_duration',
    'volunteers_count',
    'created_at',
    'updated_at'
  );

  public function table_name() {
    global $wpdb;
    return $wpdb->prefix.'csm_journal';
  }

  public function get_db_version() {
    return (int) get_option('csm_db_version', 0);
  }

  private function set_db_version($version) {
    return $this->get_db_version() === (int) $version ||
           update_option('csm_db_version', (int) $version);
  }

  public function migrate() {
    global $wpdb;
    $query_info = (object) array(
      'table_name' => $this->table_name(),
      'charset_collate' => $wpdb->get_charset_collate()
    );
    $current_db_version = $this->get_db_version();
    $migrations = array(
      function() use ($wpdb, $query_info) {
        return $wpdb->query("
          CREATE TABLE $query_info->table_name (
            id               bigint unsigned NOT NULL  PRIMARY KEY AUTO_INCREMENT,
            shift_slug       varchar(200) NOT NULL,
            volunteer_slug   varchar(200) NOT NULL,
            shift_duration   bigint unsigned NOT NULL,
            volunteers_count tinyint unsigned,
            is_frozen        tinyint(1) NOT NULL,
            created_at       bigint NOT NULL,
            updated_at       bigint NOT NULL
          ) $query_info->charset_collate;
        ");
      }
    );
    $latest_db_version = count($migrations);

    if($current_db_version < 0 || $current_db_version > $latest_db_version) {
      trigger_error(
        "CSM: unexpected database version $current_db_version",
        E_USER_WARNING
      );
      return false;
    }

    for($ver = $current_db_version; $ver < $latest_db_version; $ver++) {
      $result = call_user_func($migrations[$ver]);
      if(false === $result) {
        $mysql_error = $wpdb->last_error();
        trigger_error(
          str_squeeze("CSM: error migrating database from version
          $current_db_version to version $latest_db_version. The error
          occurred while executing migration $ver. MySQL said:
          $mysql_error."),
          E_USER_WARNING
        );
        return false;
      }
    }

    if(false === $this->set_db_version($latest_db_version)) {
      $mysql_error = $wpdb->last_error();
      $mysql_error = empty($mysql_error) ?
        'No MySQL error message' :
        "MySQL error message: $mysql_error";
      trigger_error(
        str_squeeze("CSM: error updating database version. Current version is
        $latest_db_version. $mysql_error."),
        E_USER_WARNING
      );
      return false;
    }

    return true;
  }

  public function db_clear() {
    global $wpdb;
    $table_name = $this->table_name();
    $wpdb->query("DROP TABLE IF EXISTS $table_name;");

    $this->set_db_version(0);
    return true;
  }

  public function find_all($constraints=array()) {
    global $wpdb;
    $table = $this->table_name();
    $columns = implode(",\n  ", $this->table_columns);
    $where_constraints = array_select($constraints, $this->table_columns);
    $where = $this->mk_query_str($where_constraints);
    $limit = $this->mk_limit_clause($constraints);

    $query = str_reindent("
      SELECT
        $columns
      FROM
        $table
      WHERE
        $where
      LIMIT $limit;
    ");
    $rows = $wpdb->get_results($query);

    return array_map(array($this, 'mk_entry'), $rows);
  }

  public function find($id) {
    global $wpdb;
    $table = $this->table_name();
    $columns = implode(",\n  ", $this->table_columns);

    $query = str_reindent("
      SELECT
        $columns
      FROM
        $table
      WHERE
        id = ?
      LIMIT 1;
    ");
    $row = $wpdb->get_row($wpdb->ez_prepare($query, (int) $id));

    return $this->mk_entry($row);
  }

  protected function mk_query_str($constraints) {
    global $wpdb;
    if(count($constraints) === 0)
      return '1';

    return $wpdb->ez_prepare(
      implode(" = ?,\n  ", array_keys($constraints)).' = ?',
      array_values($constraints)
    );
  }

  protected function mk_limit_clause($constraints) {
    global $wpdb;
    if(! isset($constraints['limit']))
      return PHP_INT_MAX;

    return $wpdb->prepare("%d", $constraints['limit']);
  }

  public function commit($journal_entry) {
    global $wpdb;
    $row = $this->mk_row($journal_entry);

    $table = $this->table_name();
    $query = '';
    if(is_null($row['id'])) {
      $columns = '';
      $values = '';
      $separator = ",\n  ";
      foreach($row as $column => $value) {
        $columns .= $column.$separator;
        $values .= $wpdb->ez_prepare('?'.$separator, $value);
      }
      $columns = substr($columns, 0, strlen($columns) - strlen($separator));
      $values = substr($values, 0, strlen($values) - strlen($separator));
      $query = str_reindent("
        INSERT INTO $table (
          $columns
        ) VALUES (
          $values
        );
      ");
    } else {
      $values = $this->mk_query_str($row);
      $query = $wpdb->ez_prepare(str_reindent("
        UPDATE
          $table
        SET
          $values
        WHERE
          id = ?
      "), $row['id']);
    }

    $result = $wpdb->query($query);

    if(false === $result)
      return false;

    if(! ($journal_entry->id > 0))
      $journal_entry->id = $wpdb->insert_id;
    
    return true;
  }

  protected function mk_entry($row) {
    if(is_null($row))
      return null;
    $row = (array) $row;

    // Replace timestamps with DateTime objects
    foreach(array('created_at', 'updated_at') as $col)
      $row[$col] = SimpleDateTime::fromGmtTimestamp($row[$col]);

    return new CSM_JournalEntry($row);
  }

  protected function mk_row($entry) {
    return array(
      'id' => $entry->id,
      'shift_slug' => $entry->shift_slug,
      'shift_duration' => $entry->shift_duration->seconds(),
      'volunteer_slug' => $entry->volunteer_slug,
      'volunteers_count' => $entry->volunteers_count,
      'is_frozen' => $entry->is_frozen,
      'created_at' => $entry->created_at->gmtTimestamp(),
      'updated_at' => $entry->updated_at->gmtTimestamp()
    );
  }
}
CSM_Singleton::register(new CSM_Journal());
add_action('csm_migrate_db', function() {
  $journal = CSM_Singleton::get('CSM_Journal');
  $journal->migrate();
});

?>

<?php

  namespace SLTK\Database\Repositories;

  use Exception;
  use stdClass;
  use Throwable;
  use wpdb;

  abstract class RepositoryBase {
    private static ?wpdb $db = null;

    protected static function db(): wpdb {
      if (self::$db === null) {
        global $wpdb;
        self::$db = $wpdb;
      }

      return self::$db;
    }

    /**
     * Runs $work inside a DB transaction, committing on success and rolling back and
     * re-throwing on any failure.
     *
     * @throws Throwable
     */
    public static function transaction(callable $work): mixed {
      self::db()->query('START TRANSACTION');

      try {
        $result = $work();

        self::db()->query('COMMIT');

        return $result;
      } catch (Throwable $e) {
        self::db()->query('ROLLBACK');

        throw $e;
      }
    }

    /**
     * @throws Exception
     */
    protected static function deleteById(string $tableNameWithoutPrefix, int $id, string $idColumnName = 'id'): void {
      self::db()->delete(self::prefixedTableName($tableNameWithoutPrefix), [$idColumnName => $id]);

      self::throwIfError(esc_html__('Unexpected error deleting data from database.', 'sim-league-toolkit'));
    }

    /**
     * @throws Exception
     */
    protected static function deleteFromTable(string $tableNameWithoutPrefix, string $filter): void {
      $tableName = self::prefixedTableName($tableNameWithoutPrefix);
      $sql = "DELETE FROM {$tableName} WHERE {$filter};";

      self::db()->query($sql);
      self::throwIfError(esc_html__('Unexpected error deleting rows from table', 'sim-league-toolkit'));
    }

    /**
     * @return int Number of rows affected by the query
     *
     * @throws Exception
     */
    protected static function execute(string $sql): int {

      $result = self::db()->query($sql);
      self::throwIfError(esc_html__('Unexpected error executing SQL.', 'sim-league-toolkit'));

      return $result;

    }

    /**
     * @param string|null $filter Optional WHERE expression to filter rows
     *
     * @return int Count of rows that match the filter
     *
     * @throws Exception
     */
    protected static function getCount(string $tableNameWithoutPrefix, string $filter = null): int {
      $tableName = self::prefixedTableName($tableNameWithoutPrefix);

      $query = "SELECT COUNT(*) FROM {$tableName}";
      if (isset($filter)) {
        $query .= " WHERE {$filter}";
      }

      $query .= ';';

      $queryResult = self::db()->get_var($query);

      self::throwIfError(esc_html__('Unexpected error getting count from database.', 'sim-league-toolkit'));

      return $queryResult;
    }

    /**
     * @return stdClass[]
     *
     * @throws Exception
     */
    protected static function getResults(string $query): array {
      $queryResults = self::db()->get_results($query);

      self::throwIfError(esc_html__('Unexpected error fetching results from database.', 'sim-league-toolkit'));

      return $queryResults;
    }

    /**
     * @return stdClass[]
     * @throws Exception
     */
    protected static function getResultsFromTable(string $tableNameWithoutPrefix, string $filter = null, string $sortBy = null): array {
      $query = self::selectAllQuery($tableNameWithoutPrefix, $filter, $sortBy);

      return self::getResults($query);
    }

    /**
     * @throws Exception
     */
    protected static function getRow(string $query): stdClass|null {
      $queryResult = self::db()->get_row($query);

      self::throwIfError(esc_html__('Unexpected error fetching row from database.', 'sim-league-toolkit'));

      return $queryResult;
    }

    /**
     * @throws Exception
     */
    protected static function getRowById(string $tableNameWithoutPrefix, int $id): stdClass|null {
      $tableName = self::prefixedTableName($tableNameWithoutPrefix);

      $query = "SELECT * FROM {$tableName} WHERE id = '{$id}';";

      return self::getRow($query);
    }

    /**
     * @throws Exception
     */
    protected static function getRowByName(string $tableNameWithoutPrefix, string $name): stdClass|null {
      $tableName = self::prefixedTableName($tableNameWithoutPrefix);

      $query = "SELECT * FROM {$tableName} WHERE name = '{$name}';";

      return self::getRow($query);
    }

    /**
     * @throws Exception
     */
    protected static function getRowFromTable(string $tableNameWithoutPrefix, string $filter): stdClass|null {
      $query = self::selectAllQuery($tableNameWithoutPrefix, $filter);

      return self::getRow($query);
    }

    /**
     * @throws Exception
     */
    protected static function getValue(string $query): ?string {
      $queryResult = self::db()->get_var($query);

      self::throwIfError(esc_html__('Unexpected error getting value from database.', 'sim-league-toolkit'));

      return $queryResult;
    }

    /**
     * @return int The id of the row assigned by the database
     *
     * @throws Exception
     */
    protected static function insert(string $tableNameWithoutPrefix, array $data): int {
      $tableNameWithPrefix = self::prefixedTableName($tableNameWithoutPrefix);

      self::db()->insert($tableNameWithPrefix, $data);

      self::throwIfError(esc_html__('Unexpected error inserting data into database.', 'sim-league-toolkit'));

      return self::db()->insert_id;
    }

    /**
     * @throws Exception
     */
    protected static function insertWithoutReturn(string $tableNameWithoutPrefix, array $data): void {
      $tableNameWithPrefix = self::prefixedTableName($tableNameWithoutPrefix);

      self::db()->insert($tableNameWithPrefix, $data);

      self::throwIfError(esc_html__('Unexpected error inserting data into database.', 'sim-league-toolkit'));
    }

    protected static function prefixedTableName(string $tableName): string {
      global $wpdb;

      return self::db()->prefix . $tableName;
    }

    protected static function printWpdbError(string $message): void {
      if (self::db()->last_error) { ?>
          <div class='alert alert-danger'>
              <p><?= $message; ?></p>
              <p><?= self::db()->last_error ?></p>
          </div>
        <?php
      }
    }

    protected static function selectAllQuery(string $tableNameWithoutPrefix, string $filter = null, string $sortBy = null, string $sortOrder = null): string {
      $tableName = self::prefixedTableName($tableNameWithoutPrefix);
      $query = "SELECT * FROM {$tableName}";

      if (isset($filter)) {
        $query .= " WHERE {$filter}";
      }

      if (isset($sortBy)) {
        $query .= " ORDER BY {$sortBy} ";
      }

      if (isset($sortOrder)) {
        $query .= $sortOrder;
      }

      return $query . ';';
    }

    /**
     * @return bool True if the query finds match otherwise false
     *
     * @throws Exception
     */
    protected static function simpleExists(string $tableNameWithoutPrefix, string $filter): bool {
      $tableName = self::prefixedTableName($tableNameWithoutPrefix);

      $query = "SELECT COUNT(*) FROM {$tableName} WHERE {$filter};";
      $count = (int)self::db()->get_var($query);

      self::throwIfError(esc_html__('Unexpected error inserting data into database.', 'sim-league-toolkit'));

      return $count > 0;
    }

    /**
     * @throws Exception
     */
    protected static function throwIfError(string $message): void {
      if (self::db()->last_error) {
        throw new Exception($message . ': ' . self::db()->last_error);
      }
    }

    /**
     * @throws Exception
     */
    protected static function updateById(string $tableNameWithoutPrefix, int $id, array $updates): void {

      $tableNameWithPrefix = self::prefixedTableName($tableNameWithoutPrefix);

      self::db()->update($tableNameWithPrefix, $updates, compact('id'));

      self::throwIfError(esc_html__('Unexpected error updating table by id.', 'sim-league-toolkit'));
    }
  }
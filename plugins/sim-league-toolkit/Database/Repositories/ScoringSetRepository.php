<?php

  namespace SLTK\Database\Repositories;

  use Exception;
  use SLTK\Database\TableNames;
  use stdClass;

  class ScoringSetRepository extends RepositoryBase {

    /**
     * @throws Exception
     */
    public static function add(array $scoringSet): int {
      return self::insert(TableNames::SCORING_SETS, $scoringSet);
    }

    /**
     * @throws Exception
     */
    public static function addScore(array $score): int {
      return self::insert(TableNames::SCORING_SET_SCORES, $score);
    }

    public static function getById(int $id): stdClass|null {
      return self::getRowById(TableNames::SCORING_SETS, $id);
    }

    /**
     * @throws Exception
     */
    public static function getScore(int $scoringSetId, int $position): stdClass|null {
      $tableName = self::prefixedTableName(TableNames::SCORING_SET_SCORES);
      $query = "SELECT * FROM {$tableName} WHERE scoringSetId = {$scoringSetId} AND position = {$position}";

      return self::getRow($query);
    }

    /**
     * @return stdClass[]
     */
    public static function list(): array {
      return self::getResultsFromTable(TableNames::SCORING_SETS);
    }

    /**
     * @return stdClass[]
     * @throws Exception
     */
    public static function listScores(int $scoringSetId): array {
      $tableName = self::prefixedTableName(TableNames::SCORING_SET_SCORES);

      $query = "SELECT * FROM {$tableName} WHERE scoringSetId = {$scoringSetId} ORDER BY position";

      return self::getResults($query);
    }

    public static function update(int $id, array $updates): void {
      self::updateById(TableNames::SCORING_SETS, $id, $updates);
    }

    public static function updateScore(int $id, array $updates): void {
      self::updateById(TableNames::SCORING_SET_SCORES, $id, $updates);
    }
  }
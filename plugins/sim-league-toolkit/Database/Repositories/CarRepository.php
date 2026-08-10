<?php

  namespace SLTK\Database\Repositories;

  use Exception;
  use SLTK\Database\TableNames;
  use stdClass;

  class CarRepository extends RepositoryBase {

    /**
     * @throws Exception
     */
    public static function getById(int $gameId): ?stdClass {
      return self::getRowById(TableNames::CARS, $gameId);
    }

    /**
     * @throws Exception
     */
    public static function listCarClassesForGame(int $gameId): array {
      $tableName = self::prefixedTableName(TableNames::CARS);

      $query = "SELECT DISTINCT carClass FROM $tableName WHERE gameId = $gameId";

      return self::getResults($query);
    }

    /**
     * @throws Exception
     */
    public static function listForGame(int $gameId, ?string $carClass = null): array {
      $filter = "gameId = $gameId";
      if(isset($carClass)) {
        $escapedCarClass = self::db()->prepare('%s', $carClass);
        $filter .= " AND carClass = {$escapedCarClass}";
      }

      return self::getResultsFromTable(TableNames::CARS, $filter, "name, year");
    }


    /**
     * @throws Exception
     */
    public static function list(): array {
      return self::getResultsFromTable(TableNames::CARS);
    }

    /**
     * @throws Exception
     */
    public static function listAvailableForPlan(int $planId, int $gameId): array {
      $carsTableName = self::prefixedTableName(TableNames::CARS);
      $planCarsTableName = self::prefixedTableName(TableNames::PLAN_CARS);

      $query = "SELECT c.*
                FROM $carsTableName c
                LEFT OUTER JOIN $planCarsTableName pc
                ON pc.planId = {$planId}
                AND pc.carId = c.id
                WHERE c.gameId = {$gameId}
                AND pc.carId IS NULL
                ORDER BY c.carClass, c.name;";

      return self::getResults($query);
    }
  }
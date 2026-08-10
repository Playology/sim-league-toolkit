<?php

  namespace SLTK\Database\Repositories;

  use Exception;
  use SLTK\Database\TableNames;
  use stdClass;

  class ChampionshipEventsRepository extends RepositoryBase
  {
    /**
     * @throws Exception
     */
    public static function getById(int $id): ?stdClass
    {
      $eventsTableName = self::prefixedTableName(TableNames::CHAMPIONSHIP_EVENTS);
      $championshipsTableName = self::prefixedTableName(TableNames::CHAMPIONSHIPS);
      $tracksTableName = self::prefixedTableName(TableNames::TRACKS);
      $layoutsTableName = self::prefixedTableName(TableNames::TRACK_LAYOUTS);
      $carsTableName = self::prefixedTableName(TableNames::CARS);

      $query = "SELECT
                e.*,
                c.name as championship,
                t.shortName as track,
                tl.name as trackLayout,
                car.name as trackMasterCar
            FROM {$eventsTableName} e
            LEFT JOIN {$championshipsTableName} c ON e.championshipId = c.id
            LEFT JOIN {$tracksTableName} t ON e.trackId = t.id
            LEFT JOIN {$layoutsTableName} tl ON e.trackLayoutId = tl.id
            LEFT JOIN {$carsTableName} car ON e.trackMasterCarId = car.id
            WHERE e.id = '{$id}';";

      return self::getRow($query);
    }

    /**
     * @return stdClass[]
     * @throws Exception
     */
    public static function listByChampionshipId(int $championshipId): array
    {
      $eventsTableName = self::prefixedTableName(TableNames::CHAMPIONSHIP_EVENTS);
      $championshipsTableName = self::prefixedTableName(TableNames::CHAMPIONSHIPS);
      $tracksTableName = self::prefixedTableName(TableNames::TRACKS);
      $layoutsTableName = self::prefixedTableName(TableNames::TRACK_LAYOUTS);
      $carsTableName = self::prefixedTableName(TableNames::CARS);

      $query = "SELECT
                e.*,
                c.name as championship,
                t.shortName as track,
                tl.name as trackLayout,
                car.name as trackMasterCar
            FROM {$eventsTableName} e
            LEFT JOIN {$championshipsTableName} c ON e.championshipId = c.id
            LEFT JOIN {$tracksTableName} t ON e.trackId = t.id
            LEFT JOIN {$layoutsTableName} tl ON e.trackLayoutId = tl.id
            LEFT JOIN {$carsTableName} car ON e.trackMasterCarId = car.id
            WHERE e.championshipId = '{$championshipId}'
            ORDER BY e.startDateTime;";

      return self::getResults($query);
    }

    /**
     * @throws Exception
     */
    public static function add(array $data): int
    {
      return self::insert(TableNames::CHAMPIONSHIP_EVENTS, $data);
    }

    /**
     * @throws Exception
     */
    public static function update(int $id, array $data): void
    {
      self::updateById(TableNames::CHAMPIONSHIP_EVENTS, $id, $data);
    }

    /**
     * @throws Exception
     */
    public static function delete(int $id): void
    {
      self::deleteById(TableNames::CHAMPIONSHIP_EVENTS, $id);
    }
  }

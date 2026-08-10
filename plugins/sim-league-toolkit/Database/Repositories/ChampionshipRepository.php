<?php

  namespace SLTK\Database\Repositories;

  use Exception;
  use SLTK\Core\Constants;
  use SLTK\Database\TableNames;
  use SLTK\Domain\ValueObjects\ListingFilter;
  use stdClass;

  class ChampionshipRepository extends RepositoryBase {
    /**
     * @throws Exception
     */
    public static function add(array $championship): int {
      return self::insert(TableNames::CHAMPIONSHIPS, $championship);
    }

    /**
     * @throws Exception
     */
    public static function addChampionshipClass(array $mapping): void {
      self::insertWithoutReturn(TableNames::CHAMPIONSHIP_EVENT_CLASSES, $mapping);
    }

    /**
     * @throws Exception
     */
    public static function delete(int $id): void {
      self::deleteById(TableNames::CHAMPIONSHIPS, $id);
    }

    /**
     * @throws Exception
     */
    public static function deleteClass(int $championshipId, int $eventClassId): void {
      $championshipEventClassesTableName = self::prefixedTableName(TableNames::CHAMPIONSHIP_EVENT_CLASSES);

      $query = "DELETE FROM $championshipEventClassesTableName
                WHERE championshipId = $championshipId
                AND eventClassId = $eventClassId";

      self::execute($query);
    }

    /**
     * @throws Exception
     */
    public static function getMaxEntrants(int $id): int {
      $tableName = self::prefixedTableName(TableNames::CHAMPIONSHIPS);

      $value = self::getValue(
        "SELECT maxEntrants FROM {$tableName}
         WHERE id = {$id}
         LIMIT 1;"
      );

      return $value !== null ? (int)$value : 0;
    }

    /**
     * @throws Exception
     */
    public static function getClassMaxEntrants(int $championshipId, int $eventClassId): ?int {
      $tableName = self::prefixedTableName(TableNames::CHAMPIONSHIP_EVENT_CLASSES);

      $value = self::getValue(
        "SELECT max_entrants FROM {$tableName}
         WHERE championshipId = {$championshipId}
         AND eventClassId = {$eventClassId}
         LIMIT 1;"
      );

      return $value !== null ? (int)$value : null;
    }

    /**
     * @throws Exception
     */
    public static function updateClass(int $championshipId, int $eventClassId, array $updates): void {
      $tableName = self::prefixedTableName(TableNames::CHAMPIONSHIP_EVENT_CLASSES);
      $setClauses = [];

      foreach ($updates as $column => $value) {
        $setClauses[] = $value === null ? "{$column} = NULL" : "{$column} = " . (int)$value;
      }

      $set = implode(', ', $setClauses);
      self::execute(
        "UPDATE {$tableName} SET {$set}
         WHERE championshipId = {$championshipId}
         AND eventClassId = {$eventClassId};"
      );
    }

    /**
     * @throws Exception
     */
    public static function getById(int $id): stdClass {
      $championshipsTableName = self::prefixedTableName(TableNames::CHAMPIONSHIPS);
      $gamesTableName = self::prefixedTableName(TableNames::GAMES);
      $platformsTableName = self::prefixedTableName(TableNames::PLATFORMS);
      $ruleSetsTableName = self::prefixedTableName(TableNames::RULE_SETS);
      $scoringSetsTableName = self::prefixedTableName(TableNames::SCORING_SETS);
      $tracksTableName = self::prefixedTableName(TableNames::TRACKS);
      $trackLayoutsTableName = self::prefixedTableName(TableNames::TRACK_LAYOUTS);

      $query = "SELECT c.*, p.name as platform, g.name as game, ss.name as scoringSet, r.name as ruleSet, t.shortName as trackMasterTrack, tl.name as trackMasterTrackLayout 
                FROM $championshipsTableName c
                INNER JOIN $platformsTableName p
                ON c.platformId = p.id
                INNER JOIN $gamesTableName g
                ON c.gameId = g.id
                INNER JOIN $scoringSetsTableName ss
                ON c.scoringSetId = ss.id
                LEFT OUTER JOIN $ruleSetsTableName r
                ON c.ruleSetId = r.id
                LEFT OUTER JOIN $tracksTableName t
                ON c.trackMasterTrackId = t.id
                LEFT OUTER JOIN $trackLayoutsTableName tl
                ON c.trackMasterTrackLayoutId = tl.id
                WHERE c.id = $id";

      return self::getRow($query);
    }

    /**
     * @return stdClass[]
     * @throws Exception
     */
    public static function listAll(): array {
      $championshipsTableName = self::prefixedTableName(TableNames::CHAMPIONSHIPS);
      $gamesTableName = self::prefixedTableName(TableNames::GAMES);
      $platformsTableName = self::prefixedTableName(TableNames::PLATFORMS);
      $ruleSetsTableName = self::prefixedTableName(TableNames::RULE_SETS);
      $scoringSetsTableName = self::prefixedTableName(TableNames::SCORING_SETS);
      $tracksTableName = self::prefixedTableName(TableNames::TRACKS);
      $trackLayoutsTableName = self::prefixedTableName(TableNames::TRACK_LAYOUTS);

      $query = "SELECT c.*, p.name as platform, g.name as game, ss.name as scoringSet, r.name as ruleSet, t.shortName as trackMasterTrack, tl.name as trackMasterTrackLayout 
                FROM $championshipsTableName c
                INNER JOIN $platformsTableName p
                ON c.platformId = p.id
                INNER JOIN $gamesTableName g
                ON c.gameId = g.id
                INNER JOIN $scoringSetsTableName ss
                ON c.scoringSetId = ss.id
                LEFT OUTER JOIN $ruleSetsTableName r
                ON c.ruleSetId = r.id
                LEFT OUTER JOIN $tracksTableName t
                ON c.trackMasterTrackId = t.id
                LEFT OUTER JOIN $trackLayoutsTableName tl
                ON c.trackMasterTrackLayoutId = tl.id
                ORDER BY c.isActive DESC, startDate;";

      return self::getResults($query);
    }

    /**
     * @throws Exception
     */
    public static function getMostRecentForGame(int $gameId): ?stdClass {
      $championshipsTableName = self::prefixedTableName(TableNames::CHAMPIONSHIPS);
      $gamesTableName = self::prefixedTableName(TableNames::GAMES);
      $platformsTableName = self::prefixedTableName(TableNames::PLATFORMS);
      $ruleSetsTableName = self::prefixedTableName(TableNames::RULE_SETS);
      $scoringSetsTableName = self::prefixedTableName(TableNames::SCORING_SETS);
      $tracksTableName = self::prefixedTableName(TableNames::TRACKS);
      $trackLayoutsTableName = self::prefixedTableName(TableNames::TRACK_LAYOUTS);

      $query = "SELECT c.*, p.name as platform, g.name as game, ss.name as scoringSet, r.name as ruleSet, t.shortName as trackMasterTrack, tl.name as trackMasterTrackLayout
                FROM $championshipsTableName c
                INNER JOIN $platformsTableName p
                ON c.platformId = p.id
                INNER JOIN $gamesTableName g
                ON c.gameId = g.id
                INNER JOIN $scoringSetsTableName ss
                ON c.scoringSetId = ss.id
                LEFT OUTER JOIN $ruleSetsTableName r
                ON c.ruleSetId = r.id
                LEFT OUTER JOIN $tracksTableName t
                ON c.trackMasterTrackId = t.id
                LEFT OUTER JOIN $trackLayoutsTableName tl
                ON c.trackMasterTrackLayoutId = tl.id
                WHERE c.gameId = {$gameId}
                ORDER BY c.startDate DESC
                LIMIT 1;";

      return self::getRow($query);
    }

    /**
     * @throws Exception
     */
    public static function update(int $id, array $updates): void {
      self::updateById(TableNames::CHAMPIONSHIPS, $id, $updates);
    }

    /**
     * @return stdClass[]
     * @throws Exception
     */
    public static function search(ListingFilter $filter): array {
      $championshipsTableName = self::prefixedTableName(TableNames::CHAMPIONSHIPS);
      $championshipEventsTableName = self::prefixedTableName(TableNames::CHAMPIONSHIP_EVENTS);
      $gamesTableName = self::prefixedTableName(TableNames::GAMES);
      $platformsTableName = self::prefixedTableName(TableNames::PLATFORMS);
      $ruleSetsTableName = self::prefixedTableName(TableNames::RULE_SETS);
      $scoringSetsTableName = self::prefixedTableName(TableNames::SCORING_SETS);
      $tracksTableName = self::prefixedTableName(TableNames::TRACKS);
      $trackLayoutsTableName = self::prefixedTableName(TableNames::TRACK_LAYOUTS);

      $conditions = [];
      if (!$filter->includeInactive) {
        $conditions[] = 'c.isActive = 1';
      }
      if ($filter->fromDate !== null || $filter->toDate !== null) {
        // A championship's own startDate doesn't reflect whether it's current — its events do.
        // Match if it has at least one event whose start falls within the requested range.
        $eventConditions = ['ce.championshipId = c.id'];
        if (!$filter->includeInactive) {
          $eventConditions[] = 'ce.isActive = 1';
        }
        if ($filter->fromDate !== null) {
          $eventConditions[] = self::db()->prepare('ce.startDateTime >= %s', $filter->fromDate->format(Constants::STANDARD_DATE_FORMAT));
        }
        if ($filter->toDate !== null) {
          $exclusiveUpperBound = (clone $filter->toDate)->modify('+1 day');
          $eventConditions[] = self::db()->prepare('ce.startDateTime < %s', $exclusiveUpperBound->format(Constants::STANDARD_DATE_FORMAT));
        }
        $conditions[] = "EXISTS (SELECT 1 FROM $championshipEventsTableName ce WHERE " . implode(' AND ', $eventConditions) . ')';
      }
      $where = empty($conditions) ? '' : ('WHERE ' . implode(' AND ', $conditions));

      $query = "SELECT c.*, p.name as platform, g.name as game, ss.name as scoringSet, r.name as ruleSet, t.shortName as trackMasterTrack, tl.name as trackMasterTrackLayout
                FROM $championshipsTableName c
                INNER JOIN $platformsTableName p
                ON c.platformId = p.id
                INNER JOIN $gamesTableName g
                ON c.gameId = g.id
                INNER JOIN $scoringSetsTableName ss
                ON c.scoringSetId = ss.id
                LEFT OUTER JOIN $ruleSetsTableName r
                ON c.ruleSetId = r.id
                LEFT OUTER JOIN $tracksTableName t
                ON c.trackMasterTrackId = t.id
                LEFT OUTER JOIN $trackLayoutsTableName tl
                ON c.trackMasterTrackLayoutId = tl.id
                $where
                ORDER BY c.startDate;";

      return self::getResults($query);
    }
  }
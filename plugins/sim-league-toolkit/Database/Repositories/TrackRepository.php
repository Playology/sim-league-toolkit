<?php

  namespace SLTK\Database\Repositories;

  use Exception;
  use SLTK\Database\TableNames;
  use SLTK\Domain\ValueObjects\PlanTrackFilter;
  use stdClass;

  class TrackRepository extends RepositoryBase {

    /**
     * @throws Exception
     */
    public static function getById(int $gameId): stdClass {
      return self::getRowById(TableNames::TRACKS, $gameId);
    }

    /**
     * @return stdClass[]
     * @throws Exception
     */
    public static function list(): array {
      return self::getResultsFromTable(TableNames::TRACKS);
    }

    /**
     * @return stdClass[]
     * @throws Exception
     */
    public static function listForGame(int $gameId): array {
      return self::getResultsFromTable(TableNames::TRACKS, "gameId = $gameId", 'shortName');
    }

    /**
     * @return stdClass[]
     * @throws Exception
     */
    public static function listAllLayouts(): array {
      return self::getResultsFromTable(TableNames::TRACK_LAYOUTS);
    }

    /**
     * @return stdClass[]
     * @throws Exception
     */
    public static function listLayoutsForGame(int $gameId): array {
      $trackLayoutsTableName = self::prefixedTableName(TableNames::TRACK_LAYOUTS);
      $tracksTableName = self::prefixedTableName(TableNames::TRACKS);
      $gamesTableName = self::prefixedTableName(TableNames::GAMES);

      $query = "SELECT tl.*, g.name as game, t.shortName as track
                FROM $trackLayoutsTableName tl
                INNER JOIN $tracksTableName t
                ON t.id = tl.trackId
                INNER JOIN $gamesTableName g
                ON g.id = tl.gameId
                WHERE tl.gameId = {$gameId}
                ORDER BY t.shortName, tl.name";

      return self::getResults($query);
    }

    /**
     * Plans vote at track granularity (not layout) - the specific layout for a round is picked
     * later in the Championship Builder, same as any other round. DLC/length filters both check
     * TrackLayouts uniformly for every game, including ACC - which has no real track/layout split,
     * but gets one synthetic layout row per track (see TrackLayoutsTableBuilder::loadAccLayouts())
     * purely as a metadata carrier so this filter doesn't need a second, track-level data path.
     *
     * @return stdClass[]
     * @throws Exception
     */
    public static function listAvailableForPlan(int $planId, int $gameId, PlanTrackFilter $filter): array {
      $tracksTableName = self::prefixedTableName(TableNames::TRACKS);
      $planTracksTableName = self::prefixedTableName(TableNames::PLAN_TRACKS);
      $trackLayoutsTableName = self::prefixedTableName(TableNames::TRACK_LAYOUTS);
      $championshipsTableName = self::prefixedTableName(TableNames::CHAMPIONSHIPS);
      $championshipEventsTableName = self::prefixedTableName(TableNames::CHAMPIONSHIP_EVENTS);

      $conditions = ['t.gameId = ' . $gameId, 'pt.trackId IS NULL'];

      if ($filter->excludeLastChampionshipTracks) {
        $conditions[] = "t.id NOT IN (
          SELECT ce.trackId FROM $championshipEventsTableName ce
          WHERE ce.championshipId = (
            SELECT id FROM $championshipsTableName WHERE gameId = {$gameId} ORDER BY startDate DESC LIMIT 1
          )
        )";
      }

      if ($filter->excludeDlc) {
        $conditions[] = "NOT EXISTS (
          SELECT 1 FROM $trackLayoutsTableName tl WHERE tl.trackId = t.id AND tl.dlcPack IS NOT NULL
        )";
      }

      if ($filter->minLength !== null || $filter->maxLength !== null) {
        $lengthConditions = ["tl.trackId = t.id"];
        if ($filter->minLength !== null) {
          $lengthConditions[] = "tl.length >= {$filter->minLength}";
        }
        if ($filter->maxLength !== null) {
          $lengthConditions[] = "tl.length <= {$filter->maxLength}";
        }
        $conditions[] = "EXISTS (SELECT 1 FROM $trackLayoutsTableName tl WHERE " . implode(' AND ', $lengthConditions) . ')';
      }

      $where = implode(' AND ', $conditions);

      $query = "SELECT t.*
                FROM $tracksTableName t
                LEFT OUTER JOIN $planTracksTableName pt
                ON pt.planId = {$planId}
                AND pt.trackId = t.id
                WHERE $where
                ORDER BY t.shortName;";

      return self::getResults($query);
    }

    /**
     * @return stdClass[]
     * @throws Exception
     */
    public static function listLayoutsForTrack(int $trackId): array {
      $trackLayoutsTableName = self::prefixedTableName(TableNames::TRACK_LAYOUTS);
      $tracksTableName = self::prefixedTableName(TableNames::TRACKS);
      $gamesTableName = self::prefixedTableName(TableNames::GAMES);

      $query = "SELECT tl.*, g.name as game, t.shortName as track
                FROM $trackLayoutsTableName tl
                INNER JOIN $tracksTableName t
                ON t.id = tl.trackId
                INNER JOIN $gamesTableName g
                ON g.id = tl.gameId
                WHERE tl.trackId = {$trackId}
                ORDER BY tl.name";

      return self::getResults($query);
    }


  }
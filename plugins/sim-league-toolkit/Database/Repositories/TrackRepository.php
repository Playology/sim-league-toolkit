<?php

  namespace SLTK\Database\Repositories;

  use Exception;
  use SLTK\Database\TableNames;
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
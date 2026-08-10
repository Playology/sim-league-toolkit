<?php

  namespace SLTK\Database\Repositories;

  use Exception;
  use SLTK\Core\Enums\PlanStatus;
  use SLTK\Database\TableNames;
  use stdClass;

  class ChampionshipPlanRepository extends RepositoryBase {
    /**
     * @throws Exception
     */
    public static function add(array $plan): int {
      return self::insert(TableNames::CHAMPIONSHIP_PLANS, $plan);
    }

    /**
     * @throws Exception
     */
    public static function update(int $id, array $updates): void {
      self::updateById(TableNames::CHAMPIONSHIP_PLANS, $id, $updates);
    }

    /**
     * @throws Exception
     */
    public static function delete(int $id): void {
      $planClassesTableName = self::prefixedTableName(TableNames::PLAN_CLASSES);

      self::deleteFromTable(TableNames::PLAN_CLASS_VOTES, "planClassId IN (SELECT id FROM {$planClassesTableName} WHERE planId = {$id})");
      self::deleteFromTable(TableNames::PLAN_CLASSES, "planId = {$id}");
      self::deleteFromTable(TableNames::PLAN_TRACK_VOTES, "planId = {$id}");
      self::deleteFromTable(TableNames::PLAN_TRACK_FAVOURITES, "planId = {$id}");
      self::deleteFromTable(TableNames::PLAN_TRACKS, "planId = {$id}");
      self::deleteFromTable(TableNames::PLAN_CAR_VOTES, "planId = {$id}");
      self::deleteFromTable(TableNames::PLAN_CARS, "planId = {$id}");
      self::deleteById(TableNames::CHAMPIONSHIP_PLANS, $id);
    }

    /**
     * @throws Exception
     */
    public static function getById(int $id): ?stdClass {
      $plansTableName = self::prefixedTableName(TableNames::CHAMPIONSHIP_PLANS);
      $gamesTableName = self::prefixedTableName(TableNames::GAMES);
      $platformsTableName = self::prefixedTableName(TableNames::PLATFORMS);

      $query = "SELECT p.*, g.name as game, pl.name as platform
                FROM $plansTableName p
                INNER JOIN $gamesTableName g
                ON p.gameId = g.id
                INNER JOIN $platformsTableName pl
                ON p.platformId = pl.id
                WHERE p.id = {$id};";

      return self::getRow($query);
    }

    /**
     * @return stdClass[]
     * @throws Exception
     */
    public static function listAll(): array {
      $plansTableName = self::prefixedTableName(TableNames::CHAMPIONSHIP_PLANS);
      $gamesTableName = self::prefixedTableName(TableNames::GAMES);
      $platformsTableName = self::prefixedTableName(TableNames::PLATFORMS);

      $query = "SELECT p.*, g.name as game, pl.name as platform
                FROM $plansTableName p
                INNER JOIN $gamesTableName g
                ON p.gameId = g.id
                INNER JOIN $platformsTableName pl
                ON p.platformId = pl.id
                ORDER BY p.startDate DESC;";

      return self::getResults($query);
    }

    /**
     * @return stdClass[]
     * @throws Exception
     */
    public static function listByStatus(PlanStatus $status): array {
      $plansTableName = self::prefixedTableName(TableNames::CHAMPIONSHIP_PLANS);
      $gamesTableName = self::prefixedTableName(TableNames::GAMES);
      $platformsTableName = self::prefixedTableName(TableNames::PLATFORMS);
      $escapedStatus = self::db()->prepare('%s', $status->value);

      $query = "SELECT p.*, g.name as game, pl.name as platform
                FROM $plansTableName p
                INNER JOIN $gamesTableName g
                ON p.gameId = g.id
                INNER JOIN $platformsTableName pl
                ON p.platformId = pl.id
                WHERE p.status = {$escapedStatus}
                ORDER BY p.startDate;";

      return self::getResults($query);
    }

    /**
     * @throws Exception
     */
    public static function addTrack(int $planId, int $trackId): void {
      self::db()->replace(self::prefixedTableName(TableNames::PLAN_TRACKS), [
        'planId' => $planId,
        'trackId' => $trackId,
      ]);
      self::throwIfError(esc_html__('Unexpected error adding a track to the plan.', 'sim-league-toolkit'));
    }

    /**
     * @throws Exception
     */
    public static function removeTrack(int $planId, int $trackId): void {
      self::deleteFromTable(TableNames::PLAN_TRACKS, "planId = {$planId} AND trackId = {$trackId}");
      self::deleteFromTable(TableNames::PLAN_TRACK_VOTES, "planId = {$planId} AND trackId = {$trackId}");
      self::deleteFromTable(TableNames::PLAN_TRACK_FAVOURITES, "planId = {$planId} AND trackId = {$trackId}");
    }

    /**
     * @throws Exception
     */
    public static function addCar(int $planId, int $carId): void {
      self::db()->replace(self::prefixedTableName(TableNames::PLAN_CARS), [
        'planId' => $planId,
        'carId' => $carId,
      ]);
      self::throwIfError(esc_html__('Unexpected error adding a car to the plan.', 'sim-league-toolkit'));
    }

    /**
     * @throws Exception
     */
    public static function removeCar(int $planId, int $carId): void {
      self::deleteFromTable(TableNames::PLAN_CARS, "planId = {$planId} AND carId = {$carId}");
      self::deleteFromTable(TableNames::PLAN_CAR_VOTES, "planId = {$planId} AND carId = {$carId}");
    }

    /**
     * @throws Exception
     */
    public static function addClass(int $planId, array $classData): int {
      return self::insert(TableNames::PLAN_CLASSES, array_merge(['planId' => $planId], $classData));
    }

    /**
     * @throws Exception
     */
    public static function removeClass(int $planId, int $planClassId): void {
      self::deleteFromTable(TableNames::PLAN_CLASS_VOTES, "planClassId = {$planClassId}");
      self::deleteFromTable(TableNames::PLAN_CLASSES, "id = {$planClassId} AND planId = {$planId}");
    }

    /**
     * @return stdClass[]
     * @throws Exception
     */
    public static function listTrackTallies(int $planId, int $currentUserId): array {
      $planTracksTableName = self::prefixedTableName(TableNames::PLAN_TRACKS);
      $tracksTableName = self::prefixedTableName(TableNames::TRACKS);
      $votesTableName = self::prefixedTableName(TableNames::PLAN_TRACK_VOTES);
      $favouritesTableName = self::prefixedTableName(TableNames::PLAN_TRACK_FAVOURITES);

      $query = "SELECT pt.trackId, t.shortName as trackName,
                  (SELECT COUNT(*) FROM $votesTableName v WHERE v.planId = pt.planId AND v.trackId = pt.trackId) as voteCount,
                  (SELECT COUNT(*) FROM $favouritesTableName f WHERE f.planId = pt.planId AND f.trackId = pt.trackId) as favouriteCount,
                  EXISTS(SELECT 1 FROM $votesTableName v2 WHERE v2.planId = pt.planId AND v2.trackId = pt.trackId AND v2.userId = {$currentUserId}) as currentUserVoted,
                  EXISTS(SELECT 1 FROM $favouritesTableName f2 WHERE f2.planId = pt.planId AND f2.trackId = pt.trackId AND f2.userId = {$currentUserId}) as currentUserFavourited
                FROM $planTracksTableName pt
                INNER JOIN $tracksTableName t ON pt.trackId = t.id
                WHERE pt.planId = {$planId}
                ORDER BY voteCount DESC, t.shortName;";

      return self::getResults($query);
    }

    /**
     * @return stdClass[]
     * @throws Exception
     */
    public static function listCarTallies(int $planId, int $currentUserId): array {
      $planCarsTableName = self::prefixedTableName(TableNames::PLAN_CARS);
      $carsTableName = self::prefixedTableName(TableNames::CARS);
      $votesTableName = self::prefixedTableName(TableNames::PLAN_CAR_VOTES);

      $query = "SELECT pc.carId, c.name as carName, c.carClass,
                  (SELECT COUNT(*) FROM $votesTableName v WHERE v.planId = pc.planId AND v.carId = pc.carId) as voteCount,
                  EXISTS(SELECT 1 FROM $votesTableName v2 WHERE v2.planId = pc.planId AND v2.carId = pc.carId AND v2.userId = {$currentUserId}) as currentUserVoted
                FROM $planCarsTableName pc
                INNER JOIN $carsTableName c ON pc.carId = c.id
                WHERE pc.planId = {$planId}
                ORDER BY voteCount DESC, c.carClass, c.name;";

      return self::getResults($query);
    }

    /**
     * @return stdClass[]
     * @throws Exception
     */
    public static function listClassTallies(int $planId, int $currentUserId): array {
      $planClassesTableName = self::prefixedTableName(TableNames::PLAN_CLASSES);
      $driverCategoriesTableName = self::prefixedTableName(TableNames::DRIVER_CATEGORIES);
      $carsTableName = self::prefixedTableName(TableNames::CARS);
      $usersTableName = self::prefixedTableName(TableNames::USERS);
      $votesTableName = self::prefixedTableName(TableNames::PLAN_CLASS_VOTES);

      $query = "SELECT pc.id as planClassId, pc.eventClassId, pc.name, pc.carClass, pc.driverCategoryId,
                  dc.name as driverCategoryName, pc.isSingleCarClass, pc.singleCarId, c.name as singleCarName,
                  pc.suggestedByUserId, u.display_name as suggestedByName,
                  (SELECT COUNT(*) FROM $votesTableName v WHERE v.planClassId = pc.id) as voteCount,
                  EXISTS(SELECT 1 FROM $votesTableName v2 WHERE v2.planClassId = pc.id AND v2.userId = {$currentUserId}) as currentUserVoted
                FROM $planClassesTableName pc
                LEFT OUTER JOIN $driverCategoriesTableName dc ON pc.driverCategoryId = dc.id
                LEFT OUTER JOIN $carsTableName c ON pc.singleCarId = c.id
                LEFT OUTER JOIN $usersTableName u ON pc.suggestedByUserId = u.ID
                WHERE pc.planId = {$planId}
                ORDER BY voteCount DESC, pc.name;";

      return self::getResults($query);
    }

    /**
     * @throws Exception
     */
    public static function castTrackVote(int $planId, int $trackId, int $userId): void {
      self::db()->replace(self::prefixedTableName(TableNames::PLAN_TRACK_VOTES), [
        'planId' => $planId,
        'trackId' => $trackId,
        'userId' => $userId,
      ]);
      self::throwIfError(esc_html__('Unexpected error casting a track vote.', 'sim-league-toolkit'));
    }

    /**
     * @throws Exception
     */
    public static function retractTrackVote(int $planId, int $trackId, int $userId): void {
      self::deleteFromTable(TableNames::PLAN_TRACK_VOTES, "planId = {$planId} AND trackId = {$trackId} AND userId = {$userId}");
    }

    /**
     * @throws Exception
     */
    public static function countTrackVotesForUser(int $planId, int $userId): int {
      return self::getCount(TableNames::PLAN_TRACK_VOTES, "planId = {$planId} AND userId = {$userId}");
    }

    /**
     * @throws Exception
     */
    public static function hasVotedTrack(int $planId, int $trackId, int $userId): bool {
      return self::simpleExists(TableNames::PLAN_TRACK_VOTES, "planId = {$planId} AND trackId = {$trackId} AND userId = {$userId}");
    }

    /**
     * @throws Exception
     */
    public static function addTrackFavourite(int $planId, int $trackId, int $userId): void {
      self::db()->replace(self::prefixedTableName(TableNames::PLAN_TRACK_FAVOURITES), [
        'planId' => $planId,
        'trackId' => $trackId,
        'userId' => $userId,
      ]);
      self::throwIfError(esc_html__('Unexpected error favouriting a track.', 'sim-league-toolkit'));
    }

    /**
     * @throws Exception
     */
    public static function removeTrackFavourite(int $planId, int $trackId, int $userId): void {
      self::deleteFromTable(TableNames::PLAN_TRACK_FAVOURITES, "planId = {$planId} AND trackId = {$trackId} AND userId = {$userId}");
    }

    /**
     * @throws Exception
     */
    public static function castCarVote(int $planId, int $carId, int $userId): void {
      self::db()->replace(self::prefixedTableName(TableNames::PLAN_CAR_VOTES), [
        'planId' => $planId,
        'carId' => $carId,
        'userId' => $userId,
      ]);
      self::throwIfError(esc_html__('Unexpected error casting a car vote.', 'sim-league-toolkit'));
    }

    /**
     * @throws Exception
     */
    public static function retractCarVote(int $planId, int $carId, int $userId): void {
      self::deleteFromTable(TableNames::PLAN_CAR_VOTES, "planId = {$planId} AND carId = {$carId} AND userId = {$userId}");
    }

    /**
     * @throws Exception
     */
    public static function countCarVotesForUser(int $planId, int $userId): int {
      return self::getCount(TableNames::PLAN_CAR_VOTES, "planId = {$planId} AND userId = {$userId}");
    }

    /**
     * @throws Exception
     */
    public static function hasVotedCar(int $planId, int $carId, int $userId): bool {
      return self::simpleExists(TableNames::PLAN_CAR_VOTES, "planId = {$planId} AND carId = {$carId} AND userId = {$userId}");
    }

    /**
     * @throws Exception
     */
    public static function countCarVotesForUserInClass(int $planId, int $userId, string $carClass): int {
      $carsTableName = self::prefixedTableName(TableNames::CARS);
      $votesTableName = self::prefixedTableName(TableNames::PLAN_CAR_VOTES);
      $escapedCarClass = self::db()->prepare('%s', $carClass);

      $value = self::getValue(
        "SELECT COUNT(*) FROM $votesTableName v
         INNER JOIN $carsTableName c ON v.carId = c.id
         WHERE v.planId = {$planId} AND v.userId = {$userId} AND c.carClass = {$escapedCarClass};"
      );

      return (int)$value;
    }

    /**
     * @throws Exception
     */
    public static function castClassVote(int $planClassId, int $userId): void {
      self::db()->replace(self::prefixedTableName(TableNames::PLAN_CLASS_VOTES), [
        'planClassId' => $planClassId,
        'userId' => $userId,
      ]);
      self::throwIfError(esc_html__('Unexpected error casting a class vote.', 'sim-league-toolkit'));
    }

    /**
     * @throws Exception
     */
    public static function retractClassVote(int $planClassId, int $userId): void {
      self::deleteFromTable(TableNames::PLAN_CLASS_VOTES, "planClassId = {$planClassId} AND userId = {$userId}");
    }

    /**
     * @throws Exception
     */
    public static function hasVotedClass(int $planClassId, int $userId): bool {
      return self::simpleExists(TableNames::PLAN_CLASS_VOTES, "planClassId = {$planClassId} AND userId = {$userId}");
    }

    /**
     * @throws Exception
     */
    public static function hasFavouritedTrack(int $planId, int $trackId, int $userId): bool {
      return self::simpleExists(TableNames::PLAN_TRACK_FAVOURITES, "planId = {$planId} AND trackId = {$trackId} AND userId = {$userId}");
    }

    /**
     * @throws Exception
     */
    public static function planClassBelongsToPlan(int $planId, int $planClassId): bool {
      return self::simpleExists(TableNames::PLAN_CLASSES, "id = {$planClassId} AND planId = {$planId}");
    }
  }

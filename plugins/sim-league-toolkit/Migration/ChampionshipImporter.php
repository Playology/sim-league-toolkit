<?php

  namespace SLTK\Migration;

  use DateTime;
  use Exception;
  use SLTK\Core\Constants;
  use SLTK\Core\Enums\ChampionshipType;
  use SLTK\Core\Enums\GameKey;
  use SLTK\Database\Repositories\MigrationRecordsRepository;
  use SLTK\Domain\Championship;
  use SLTK\Domain\ChampionshipEntry;
  use stdClass;

  /**
   * Migrates ACCLT's championships - the championship itself, its classes and its season-long
   * entrant roster (`acclt_championship_entrants`, distinct from per-event attendance, which has no
   * SLTK equivalent for championship events). Events/sessions are migrated separately by
   * `ChampionshipEventImporter`, once the championship this importer creates exists to attach to.
   */
  class ChampionshipImporter implements MigrationImporter {
    private const string ENTITY_KEY = 'championship';
    private const string SCORING_SET_ENTITY_KEY = 'scoring-set';

    private CarClassResolver $carClassResolver;
    private DriverCategoryLookup $driverCategoryLookup;
    private EventClassCatalog $eventClassCatalog;
    private GameKeyLookup $gameKeyLookup;
    private ServerImportSupport $serverImportSupport;
    private TrackResolver $trackResolver;

    public function getEntityKey(): string {
      return self::ENTITY_KEY;
    }

    public function getLabel(): string {
      return __('Championships', 'sim-league-toolkit');
    }

    /**
     * @throws Exception
     */
    public function run(): MigrationRunResult {
      $result = new MigrationRunResult();

      $this->carClassResolver = new CarClassResolver();
      $this->driverCategoryLookup = new DriverCategoryLookup();
      $this->eventClassCatalog = new EventClassCatalog();
      $this->gameKeyLookup = new GameKeyLookup();
      $this->serverImportSupport = new ServerImportSupport();
      $this->trackResolver = new TrackResolver();

      $classesByChampionshipId = $this->groupByChampionshipId(AccltLegacyDatabase::getChampionshipClasses());
      $entrantsByChampionshipId = $this->groupByChampionshipId(AccltLegacyDatabase::getChampionshipEntrants());

      foreach (AccltLegacyDatabase::getChampionships() as $legacyChampionship) {
        $legacyId = (int)$legacyChampionship->id;
        $this->migrateChampionship(
          $legacyChampionship,
          $classesByChampionshipId[$legacyId] ?? [],
          $entrantsByChampionshipId[$legacyId] ?? [],
          $result
        );
      }

      return $result;
    }

    /**
     * @return array<int, stdClass[]> legacy championshipId => rows
     */
    private function groupByChampionshipId(array $rows): array {
      $grouped = [];

      foreach ($rows as $row) {
        $grouped[(int)$row->championshipId][] = $row;
      }

      return $grouped;
    }

    /**
     * @throws Exception
     */
    private function migrateChampionship(stdClass $legacyChampionship, array $legacyClasses, array $legacyEntrants, MigrationRunResult $result): void {
      $legacyId = (int)$legacyChampionship->id;

      try {
        if (MigrationRecordsRepository::isMigrated(self::ENTITY_KEY, $legacyId)) {
          $result->recordSkipped();
          return;
        }

        $legacyGameType = $legacyChampionship->gameType ?? GameKey::AssettoCorsaCompetizione->value;
        $gameId = $this->gameKeyLookup->resolve($legacyGameType);
        if ($gameId === null) {
          throw new Exception(sprintf('unknown game "%s"', $legacyGameType));
        }

        $legacyPlatformId = !empty($legacyChampionship->platformId) ? (int)$legacyChampionship->platformId : null;

        $championship = new Championship();
        $championship->setName($legacyChampionship->name);
        $championship->setDescription($legacyChampionship->description ?? '');
        $championship->setBannerImageUrl($legacyChampionship->bannerImageUrl ?? '');
        $championship->setGameId($gameId);
        $championship->setPlatformId($this->serverImportSupport->getPlatformId($legacyPlatformId));
        $championship->setIsActive((bool)$legacyChampionship->isActive);
        $championship->setStartDate($this->resolveStartDate($legacyChampionship));
        $championship->setMaxEntrants((int)($legacyChampionship->maxCarSlots ?? 0));
        $championship->setAllowEntryChange((bool)($legacyChampionship->allowEntryChange ?? false));
        $championship->setEntryChangeLimit((int)($legacyChampionship->entryChangeLimit ?? 0));
        $championship->setResultsToDiscard((int)($legacyChampionship->resultsToDiscard ?? 0));
        $championship->setScoringSetId($this->resolveScoringSetId($legacyClasses) ?? Constants::DEFAULT_ID);

        if (!empty($legacyChampionship->isTrackMasterChampionship)) {
          [$trackMasterTrackId, $trackMasterTrackLayoutId] = $this->trackResolver->resolve((int)$legacyChampionship->trackMasterTrackId, $gameId);
          $championship->setChampionshipType(ChampionshipType::TrackMaster);
          $championship->setTrackMasterTrackId($trackMasterTrackId);
          $championship->setTrackMasterTrackLayoutId($trackMasterTrackLayoutId);
        }

        $championship->save();

        MigrationRecordsRepository::recordMigration(self::ENTITY_KEY, $legacyId, $championship->getId());

        $eventClassIdsByLegacyId = $this->migrateChampionshipClasses($legacyClasses, $gameId, $championship->getId());
        $this->migrateEntrants($legacyEntrants, $gameId, $championship->getId(), $eventClassIdsByLegacyId, $result);

        $result->recordMigrated();
      } catch (Exception $e) {
        $result->recordFailed(sprintf(__('Championship %1$d (%2$s): %3$s', 'sim-league-toolkit'), $legacyId, $legacyChampionship->name ?? '', $e->getMessage()));
      }
    }

    /**
     * @return array<int, int> legacy championshipClassId => SLTK eventClassId
     * @throws Exception
     */
    private function migrateChampionshipClasses(array $legacyClasses, int $gameId, int $championshipId): array {
      $idsByLegacyId = [];

      foreach ($legacyClasses as $legacyClass) {
        $driverCategoryId = $this->driverCategoryLookup->resolve($legacyClass->driverCategory);
        if ($driverCategoryId === null) {
          throw new Exception(sprintf('unknown driver category "%s" for class "%s"', $legacyClass->driverCategory, $legacyClass->name));
        }

        [$carClass, $singleCarId] = $this->carClassResolver->resolveCarClassAndSingleCarId(
          (bool)$legacyClass->isSingleCarClass,
          $legacyClass->carClass,
          (int)($legacyClass->singleCarId ?? 0),
          $gameId
        );

        $eventClassId = $this->eventClassCatalog->resolveOrCreate($legacyClass->name, $gameId, $driverCategoryId, $carClass, (bool)$legacyClass->isSingleCarClass, $singleCarId);

        Championship::addChampionshipClass($championshipId, $eventClassId);
        $idsByLegacyId[(int)$legacyClass->id] = $eventClassId;
      }

      return $idsByLegacyId;
    }

    /**
     * @throws Exception
     */
    private function migrateEntrants(array $legacyEntrants, int $gameId, int $championshipId, array $eventClassIdsByLegacyId, MigrationRunResult $result): void {
      foreach ($legacyEntrants as $legacyEntrant) {
        $userId = (int)$legacyEntrant->userId;

        if (get_user_by('id', $userId) === false) {
          $result->recordSkipped(sprintf(__('Entrant: user %d no longer exists, skipped.', 'sim-league-toolkit'), $userId));
          continue;
        }

        $eventClassId = $eventClassIdsByLegacyId[(int)$legacyEntrant->classId] ?? null;
        if ($eventClassId === null) {
          $result->recordFailed(sprintf(__('Entrant (user %1$d): class (legacy id %2$d) was not migrated, skipped.', 'sim-league-toolkit'), $userId, (int)$legacyEntrant->classId));
          continue;
        }

        $carId = $this->carClassResolver->resolveCarId((int)$legacyEntrant->carId, $gameId);
        if ($carId === null) {
          $result->recordFailed(sprintf(__('Entrant (user %1$d): car (legacy id %2$d) could not be matched, skipped.', 'sim-league-toolkit'), $userId, (int)$legacyEntrant->carId));
          continue;
        }

        $entry = new ChampionshipEntry();
        $entry->setChampionshipId($championshipId);
        $entry->setCarId($carId);
        $entry->setUserId($userId);
        $entry->setStatus('confirmed');
        $entry->setEventClassId($eventClassId);
        $entry->save();
      }
    }

    /**
     * @throws Exception
     */
    private function resolveScoringSetId(array $legacyClasses): ?int {
      foreach ($legacyClasses as $legacyClass) {
        if (empty($legacyClass->scoringSetId)) {
          continue;
        }

        $targetId = MigrationRecordsRepository::getTargetId(self::SCORING_SET_ENTITY_KEY, (int)$legacyClass->scoringSetId);
        if ($targetId !== null) {
          return $targetId;
        }
      }

      return null;
    }

    private function resolveStartDate(stdClass $legacyChampionship): DateTime {
      $date = DateTime::createFromFormat('Y-m-d', $legacyChampionship->startDate);

      return $date ?: new DateTime();
    }
  }

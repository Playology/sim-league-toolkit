<?php

  namespace SLTK\Migration;

  use DateTime;
  use DateTimeZone;
  use Exception;
  use SLTK\Core\Enums\GameKey;
  use SLTK\Database\Repositories\MigrationRecordsRepository;
  use SLTK\Domain\ChampionshipEvent;
  use stdClass;

  /**
   * Migrates ACCLT's championship events (and their sessions) for championships already migrated by
   * `ChampionshipImporter` - run after it, since a legacy championshipId must already resolve to a
   * real SLTK championship. No per-event entrant migration here: SLTK has no per-round attendance
   * concept for championships, only the season-long roster `ChampionshipImporter` already migrated.
   */
  class ChampionshipEventImporter implements MigrationImporter {
    private const string ENTITY_KEY = 'championship-event';
    private const string CHAMPIONSHIP_ENTITY_KEY = 'championship';

    private CarClassResolver $carClassResolver;
    private EventSessionMigrator $eventSessionMigrator;
    private GameKeyLookup $gameKeyLookup;
    private TrackResolver $trackResolver;

    public function getEntityKey(): string {
      return self::ENTITY_KEY;
    }

    public function getLabel(): string {
      return __('Championship events', 'sim-league-toolkit');
    }

    /**
     * @throws Exception
     */
    public function run(): MigrationRunResult {
      $result = new MigrationRunResult();

      $this->carClassResolver = new CarClassResolver();
      $this->eventSessionMigrator = new EventSessionMigrator();
      $this->gameKeyLookup = new GameKeyLookup();
      $this->trackResolver = new TrackResolver();

      $sessionsByEventId = $this->groupByEventId(AccltLegacyDatabase::getEventSessions());

      foreach (AccltLegacyDatabase::getEvents() as $legacyEvent) {
        if (empty($legacyEvent->championshipId)) {
          continue;
        }

        $legacyId = (int)$legacyEvent->id;
        $this->migrateEvent($legacyEvent, $sessionsByEventId[$legacyId] ?? [], $result);
      }

      return $result;
    }

    /**
     * @return array<int, stdClass[]> legacy eventId => rows
     */
    private function groupByEventId(array $rows): array {
      $grouped = [];

      foreach ($rows as $row) {
        $grouped[(int)$row->eventId][] = $row;
      }

      return $grouped;
    }

    /**
     * @throws Exception
     */
    private function migrateEvent(stdClass $legacyEvent, array $legacySessions, MigrationRunResult $result): void {
      $legacyId = (int)$legacyEvent->id;

      if (!empty($legacyEvent->isTeamEvent)) {
        $result->recordSkipped(sprintf(__('Event %1$d (%2$s): team event, SLTK has no team concept yet, skipped.', 'sim-league-toolkit'), $legacyId, $legacyEvent->name));
        return;
      }

      try {
        if (MigrationRecordsRepository::isMigrated(self::ENTITY_KEY, $legacyId)) {
          $result->recordSkipped();
          return;
        }

        $championshipId = MigrationRecordsRepository::getTargetId(self::CHAMPIONSHIP_ENTITY_KEY, (int)$legacyEvent->championshipId);
        if ($championshipId === null) {
          $result->recordSkipped(sprintf(__('Event %1$d (%2$s): parent championship (legacy id %3$d) was not migrated, skipped.', 'sim-league-toolkit'), $legacyId, $legacyEvent->name, (int)$legacyEvent->championshipId));
          return;
        }

        $legacyGameType = $legacyEvent->gameType ?? GameKey::AssettoCorsaCompetizione->value;
        $gameId = $this->gameKeyLookup->resolve($legacyGameType);
        if ($gameId === null) {
          throw new Exception(sprintf('unknown game "%s"', $legacyGameType));
        }

        [$trackId, $trackLayoutId] = $this->trackResolver->resolve((int)$legacyEvent->trackId, $gameId);

        $championshipEvent = new ChampionshipEvent();
        $championshipEvent->setChampionshipId($championshipId);
        $championshipEvent->setTrackId($trackId);
        $championshipEvent->setTrackLayoutId($trackLayoutId);
        $championshipEvent->setName($legacyEvent->name);
        $championshipEvent->setStartDateTime($this->resolveStartDateTime($legacyEvent));
        $championshipEvent->setIsActive((bool)$legacyEvent->isActive);
        $championshipEvent->setBannerImageUrl($legacyEvent->bannerImageUrl ?? '');

        if (!empty($legacyEvent->isTrackMasterEvent)) {
          $trackMasterCarId = $this->carClassResolver->resolveCarId((int)($legacyEvent->trackMasterCarId ?? 0), $gameId);
          if ($trackMasterCarId === null) {
            throw new Exception(sprintf('Track Master car (legacy id %d) could not be matched to an SLTK car', (int)($legacyEvent->trackMasterCarId ?? 0)));
          }

          $championshipEvent->setTrackMasterCarId($trackMasterCarId);
        }

        $championshipEvent->save();

        MigrationRecordsRepository::recordMigration(self::ENTITY_KEY, $legacyId, $championshipEvent->getId());

        $this->eventSessionMigrator->migrate($legacySessions, $legacyGameType, $championshipEvent->getEventRefId());

        $result->recordMigrated();
      } catch (Exception $e) {
        $result->recordFailed(sprintf(__('Event %1$d (%2$s): %3$s', 'sim-league-toolkit'), $legacyId, $legacyEvent->name ?? '', $e->getMessage()));
      }
    }

    private function resolveStartDateTime(stdClass $legacyEvent): DateTime {
      $time = substr($legacyEvent->startTime ?? '00:00:00', 0, 8);
      $date = DateTime::createFromFormat('Y-m-d H:i:s', $legacyEvent->startDate . ' ' . $time, new DateTimeZone('UTC'));

      return $date ?: new DateTime('now', new DateTimeZone('UTC'));
    }
  }

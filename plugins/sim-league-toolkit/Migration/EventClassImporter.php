<?php

  namespace SLTK\Migration;

  use Exception;
  use SLTK\Database\Repositories\MigrationRecordsRepository;
  use SLTK\Domain\DriverCategory;
  use SLTK\Domain\EventClass;
  use SLTK\Domain\Game;
  use stdClass;

  class EventClassImporter implements MigrationImporter {
    private const string ENTITY_KEY = 'event-class';

    public function getEntityKey(): string {
      return self::ENTITY_KEY;
    }

    public function getLabel(): string {
      return __('Event classes', 'sim-league-toolkit');
    }

    public function run(): MigrationRunResult {
      $result = new MigrationRunResult();
      $gameIdsByKey = $this->buildGameIdsByKey();
      $driverCategoryIdsByName = $this->buildDriverCategoryIdsByName();
      $carClassResolver = new CarClassResolver();

      foreach (AccltLegacyDatabase::getCarDriverClasses() as $legacyClass) {
        $this->migrateClass($legacyClass, $gameIdsByKey, $driverCategoryIdsByName, $carClassResolver, $result);
      }

      return $result;
    }

    private function buildDriverCategoryIdsByName(): array {
      $lookup = [];

      foreach (DriverCategory::list() as $category) {
        $lookup[$category->getName()] = $category->getId();
      }

      return $lookup;
    }

    private function buildGameIdsByKey(): array {
      $lookup = [];

      foreach (Game::list() as $game) {
        $lookup[$game->getGameKey()] = $game->getId();
      }

      return $lookup;
    }

    private function migrateClass(stdClass $legacyClass, array $gameIdsByKey, array $driverCategoryIdsByName, CarClassResolver $carClassResolver, MigrationRunResult $result): void {
      $legacyId = (int)$legacyClass->id;

      if (!empty($legacyClass->eventId)) {
        $result->recordSkipped(sprintf(__('Car driver class %1$d (%2$s): tied to a specific legacy event, not a reusable template, skipped.', 'sim-league-toolkit'), $legacyId, $legacyClass->name));
        return;
      }

      try {
        if (MigrationRecordsRepository::isMigrated(self::ENTITY_KEY, $legacyId)) {
          $result->recordSkipped();
          return;
        }

        $gameId = $gameIdsByKey[$legacyClass->game] ?? null;
        if ($gameId === null) {
          throw new Exception(sprintf('unknown game "%s"', $legacyClass->game));
        }

        $driverCategoryId = $driverCategoryIdsByName[$legacyClass->driverCategory] ?? null;
        if ($driverCategoryId === null) {
          throw new Exception(sprintf('unknown driver category "%s"', $legacyClass->driverCategory));
        }

        [$carClass, $singleCarId] = $carClassResolver->resolveCarClassAndSingleCarId((bool)$legacyClass->isSingleCarClass, $legacyClass->carClass, (int)$legacyClass->singleCarId, $gameId);

        $eventClass = new EventClass();
        $eventClass->setName($legacyClass->name);
        $eventClass->setGameId($gameId);
        $eventClass->setDriverCategoryId($driverCategoryId);
        $eventClass->setCarClass($carClass);
        $eventClass->setIsSingleCarClass((bool)$legacyClass->isSingleCarClass);
        $eventClass->setSingleCarId($singleCarId);
        $eventClass->setIsBuiltIn(false);
        $eventClass->save();

        MigrationRecordsRepository::recordMigration(self::ENTITY_KEY, $legacyId, $eventClass->getId());
        $result->recordMigrated();
      } catch (Exception $e) {
        $result->recordFailed(sprintf(__('Car driver class %1$d (%2$s): %3$s', 'sim-league-toolkit'), $legacyId, $legacyClass->name ?? '', $e->getMessage()));
      }
    }
  }

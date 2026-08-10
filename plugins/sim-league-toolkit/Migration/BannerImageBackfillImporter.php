<?php

  namespace SLTK\Migration;

  use SLTK\Core\BannerImageProvider;
  use SLTK\Domain\Championship;
  use SLTK\Domain\ChampionshipEvent;
  use SLTK\Domain\StandaloneEvent;

  /**
   * Sweeps championships, championship events and standalone events for an empty `bannerImageUrl`
   * and assigns a random one. Not a legacy-row importer: earlier ACCLT versions computed a random
   * banner per page load instead of storing one, so migrated rows can carry an empty value straight
   * through. Runs every "Migrate" pass (after the entity importers, so it also catches rows
   * migrated in the same run) and is safe to run repeatedly - already-set banners are untouched, and
   * there's no legacy source id to track via `sltk_migration_records`.
   */
  class BannerImageBackfillImporter implements MigrationImporter {
    private const string ENTITY_KEY = 'banner-image-backfill';

    public function getEntityKey(): string {
      return self::ENTITY_KEY;
    }

    public function getLabel(): string {
      return __('Banner images', 'sim-league-toolkit');
    }

    public function run(): MigrationRunResult {
      $result = new MigrationRunResult();

      foreach (Championship::list() as $championship) {
        $this->backfillIfEmpty($championship, $result);

        foreach (Championship::listEvents($championship->getId()) as $championshipEvent) {
          $this->backfillIfEmpty($championshipEvent, $result);
        }
      }

      foreach (StandaloneEvent::list() as $standaloneEvent) {
        $this->backfillIfEmpty($standaloneEvent, $result);
      }

      return $result;
    }

    private function backfillIfEmpty(Championship|ChampionshipEvent|StandaloneEvent $entity, MigrationRunResult $result): void {
      if ($entity->getBannerImageUrl() !== '') {
        return;
      }

      $entity->setBannerImageUrl(BannerImageProvider::getRandomBannerImageUrl());
      $entity->save();

      $result->recordMigrated();
    }
  }

<?php

  namespace SLTK\Migration;

  class MigrationRegistry {
    public static function find(string $entityKey): ?MigrationImporter {
      foreach (self::all() as $importer) {
        if ($importer->getEntityKey() === $entityKey) {
          return $importer;
        }
      }

      return null;
    }

    /**
     * @return MigrationImporter[]
     */
    public static function all(): array {
      return [
        new MemberProfileImporter(),
        new ScoringSetImporter(),
        new AccServerImporter(),
        new Ams2ServerImporter(),
        new EventClassImporter(),
        new StandaloneEventImporter(),
        new ChampionshipImporter(),
        new ChampionshipEventImporter(),
        new BannerImageBackfillImporter(),
        new StandaloneSessionResultImporter(),
        new ChampionshipSessionResultImporter(),
        new TrophyImporter(),
      ];
    }
  }

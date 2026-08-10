<?php

  namespace SLTK\Database;

  class ChampionshipEventsTableBuilder extends TableBuilder {
    public function addConstraints(string $tablePrefix): void {
      global $wpdb;
      $tableName = $this->tableName($tablePrefix);

      if (empty($wpdb->get_results("SHOW COLUMNS FROM {$tableName} LIKE 'trackMasterCarId'"))) {
        $wpdb->query("ALTER TABLE {$tableName} ADD COLUMN trackMasterCarId BIGINT NULL");
      }

      $this->addSimpleForeignKey($tablePrefix, TableNames::EVENT_REFS, 'eventRefId');
      $this->addSimpleForeignKey($tablePrefix, TableNames::CHAMPIONSHIPS, 'championshipId');
      $this->addSimpleForeignKey($tablePrefix, TableNames::CARS, 'trackMasterCarId');
    }

    public function applyAdjustments(string $tablePrefix): void {
      global $wpdb;
      $tableName = $this->tableName($tablePrefix);

      if (empty($wpdb->get_results("SHOW COLUMNS FROM {$tableName} LIKE 'trophiesAwarded'"))) {
        $wpdb->query("ALTER TABLE {$tableName} ADD COLUMN trophiesAwarded BOOLEAN NOT NULL DEFAULT false");
      }

      if (empty($wpdb->get_results("SHOW COLUMNS FROM {$tableName} LIKE 'isTeamEvent'"))) {
        $wpdb->query("ALTER TABLE {$tableName} ADD COLUMN isTeamEvent BOOLEAN NOT NULL DEFAULT false");
      }

      if (empty($wpdb->get_results("SHOW COLUMNS FROM {$tableName} LIKE 'maxTeamSize'"))) {
        $wpdb->query("ALTER TABLE {$tableName} ADD COLUMN maxTeamSize TINYINT NOT NULL DEFAULT 0");
      }
    }

    public function definitionSql(string $tablePrefix, string $charsetCollate): string {
      $tableName = $this->tableName($tablePrefix);

      return "CREATE TABLE {$tableName} (
            id BIGINT NOT NULL AUTO_INCREMENT,
            eventRefId BIGINT NOT NULL,
            championshipId BIGINT NOT NULL,
            trackId BIGINT NOT NULL,
            trackLayoutId BIGINT NULL,
            trackMasterCarId BIGINT NULL,
            name VARCHAR(255) NOT NULL,
            startDateTime DATETIME NOT NULL,
            isActive BOOLEAN NOT NULL DEFAULT false,
            isCompleted BOOLEAN NOT NULL DEFAULT false,
            bannerImageUrl TINYTEXT NULL,
            PRIMARY KEY (id),
            INDEX idx_championship (championshipId),
            INDEX idx_event_ref (eventRefId)
        ) {$charsetCollate};";
    }

    public function initialData(string $tablePrefix): void {
    }

    public function tableName(string $tablePrefix): string {
      return $tablePrefix . TableNames::CHAMPIONSHIP_EVENTS;
    }
  }

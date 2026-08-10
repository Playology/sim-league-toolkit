<?php

  namespace SLTK\Database;

  class StandaloneEventsTableBuilder extends TableBuilder {

    public function addConstraints(string $tablePrefix): void {
      $this->addSimpleForeignKey($tablePrefix, TableNames::EVENT_REFS, 'eventRefId');
      $this->addSimpleForeignKey($tablePrefix, TableNames::GAMES, 'gameId');
      $this->addSimpleForeignKey($tablePrefix, TableNames::TRACKS, 'trackId');
      $this->addSimpleForeignKey($tablePrefix, TableNames::TRACK_LAYOUTS, 'trackLayoutId');
      $this->addSimpleForeignKey($tablePrefix, TableNames::SCORING_SETS, 'scoringSetId');
      $this->addSimpleForeignKey($tablePrefix, TableNames::RULE_SETS, 'ruleSetId');
    }

    public function applyAdjustments(string $tablePrefix): void {
      global $wpdb;
      $tableName = $this->tableName($tablePrefix);

      if (empty($wpdb->get_results("SHOW COLUMNS FROM {$tableName} LIKE 'trophiesAwarded'"))) {
        $wpdb->query("ALTER TABLE {$tableName} ADD COLUMN trophiesAwarded BIT NOT NULL DEFAULT 0");
      }

      if (empty($wpdb->get_results("SHOW COLUMNS FROM {$tableName} LIKE 'isTeamEvent'"))) {
        $wpdb->query("ALTER TABLE {$tableName} ADD COLUMN isTeamEvent BIT NOT NULL DEFAULT 0");
      }

      if (empty($wpdb->get_results("SHOW COLUMNS FROM {$tableName} LIKE 'maxTeamSize'"))) {
        $wpdb->query("ALTER TABLE {$tableName} ADD COLUMN maxTeamSize TINYINT NOT NULL DEFAULT 0");
      }
    }

    public function definitionSql(string $tablePrefix, string $charsetCollate): string {
      $tableName = $this->tableName($tablePrefix);

      return "CREATE TABLE {$tableName} (
        id bigint NOT NULL AUTO_INCREMENT,
        eventRefId bigint NULL,
        name tinytext NOT NULL,
        description text NOT NULL,
        bannerImageUrl tinytext NULL,
        gameId bigint NOT NULL,
        trackId bigint NOT NULL,
        trackLayoutId bigint NULL,
        eventDate date NOT NULL,
        startTime varchar(5) NOT NULL DEFAULT '',
        isActive bit NOT NULL DEFAULT 0,
        scoringSetId bigint NULL,
        ruleSetId bigint NULL,
        maxEntrants smallint NOT NULL DEFAULT 0,
        isPublic bit NOT NULL DEFAULT 1,
        PRIMARY KEY  (id)
      ) {$charsetCollate};";
    }

    public function initialData(string $tablePrefix): void {
    }

    public function tableName(string $tablePrefix): string {
      return $tablePrefix . TableNames::STANDALONE_EVENTS;
    }
  }

<?php

  namespace SLTK\Database;

  class ChampionshipEntriesTableBuilder extends TableBuilder {

    public function addConstraints(string $tablePrefix): void {
      $this->addSimpleForeignKey($tablePrefix, TableNames::CHAMPIONSHIPS, 'championshipId');
      $this->addSimpleForeignKey($tablePrefix, TableNames::EVENT_CLASSES, 'eventClassId');
      $this->addSimpleForeignKey($tablePrefix, TableNames::CARS, 'carId');
      $this->addSimpleForeignKey($tablePrefix, TableNames::USERS, 'userId');
    }

    public function applyAdjustments(string $tablePrefix): void {
      global $wpdb;
      $tableName = $this->tableName($tablePrefix);

      if (empty($wpdb->get_results("SHOW COLUMNS FROM {$tableName} LIKE 'status'"))) {
        $wpdb->query("ALTER TABLE {$tableName} ADD COLUMN status ENUM('confirmed', 'waitlisted') NOT NULL DEFAULT 'confirmed'");
      }

      if (empty($wpdb->get_results("SHOW COLUMNS FROM {$tableName} LIKE 'created_at'"))) {
        $wpdb->query("ALTER TABLE {$tableName} ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");
      }

      if (empty($wpdb->get_results("SHOW COLUMNS FROM {$tableName} LIKE 'teamName'"))) {
        $wpdb->query("ALTER TABLE {$tableName} ADD COLUMN teamName TINYTEXT NULL");
      }
    }

    public function definitionSql(string $tablePrefix, string $charsetCollate): string {
      $tableName = $this->tableName($tablePrefix);

      return "CREATE TABLE {$tableName} (
          id bigint NOT NULL AUTO_INCREMENT,
          championshipId bigint NOT NULL,
          eventClassId bigint NOT NULL,
          carId bigint NOT NULL,
          userId bigint unsigned NOT NULL,
          PRIMARY KEY  (id)
        ) {$charsetCollate};";
    }

    public function initialData(string $tablePrefix): void {

    }

    public function tableName(string $tablePrefix): string {
      return $tablePrefix . TableNames::CHAMPIONSHIP_ENTRIES;
    }
  }
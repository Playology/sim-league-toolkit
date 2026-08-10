<?php

  namespace SLTK\Database;

  class EventTeamMembersTableBuilder extends TableBuilder {

    public function addConstraints(string $tablePrefix): void {
      $this->addSimpleForeignKey($tablePrefix, TableNames::USERS, 'memberId');
    }

    public function applyAdjustments(string $tablePrefix): void {
    }

    public function definitionSql(string $tablePrefix, string $charsetCollate): string {
      $tableName = $this->tableName($tablePrefix);

      return "CREATE TABLE {$tableName} (
          id BIGINT NOT NULL AUTO_INCREMENT,
          entryScope VARCHAR(20) NOT NULL,
          entryId BIGINT NOT NULL,
          memberId BIGINT UNSIGNED NOT NULL,
          PRIMARY KEY (id),
          INDEX idx_entry (entryScope, entryId)
        ) {$charsetCollate};";
    }

    public function initialData(string $tablePrefix): void {
    }

    public function tableName(string $tablePrefix): string {
      return $tablePrefix . TableNames::EVENT_TEAM_MEMBERS;
    }
  }

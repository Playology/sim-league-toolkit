<?php

  namespace SLTK\Database;

  class TeamRequestsTableBuilder extends TableBuilder {

    public function addConstraints(string $tablePrefix): void {
      $this->addSimpleForeignKey($tablePrefix, TableNames::TEAMS, 'teamId');
      $this->addSimpleForeignKey($tablePrefix, TableNames::USERS, 'memberId');
    }

    public function applyAdjustments(string $tablePrefix): void {
    }

    public function definitionSql(string $tablePrefix, string $charsetCollate): string {
      $tableName = $this->tableName($tablePrefix);

      return "CREATE TABLE {$tableName} (
          id BIGINT NOT NULL AUTO_INCREMENT,
          teamId BIGINT NOT NULL,
          memberId BIGINT UNSIGNED NOT NULL,
          pending BOOLEAN NOT NULL DEFAULT true,
          rejected BOOLEAN NOT NULL DEFAULT false,
          accepted BOOLEAN NOT NULL DEFAULT false,
          PRIMARY KEY (id),
          INDEX idx_team (teamId),
          INDEX idx_member (memberId)
        ) {$charsetCollate};";
    }

    public function initialData(string $tablePrefix): void {
    }

    public function tableName(string $tablePrefix): string {
      return $tablePrefix . TableNames::TEAM_REQUESTS;
    }
  }

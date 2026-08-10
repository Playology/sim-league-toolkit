<?php

  namespace SLTK\Database;

  class TeamsTableBuilder extends TableBuilder {

    public function addConstraints(string $tablePrefix): void {
      $this->addSimpleForeignKey($tablePrefix, TableNames::USERS, 'ownerId');
    }

    public function applyAdjustments(string $tablePrefix): void {
    }

    public function definitionSql(string $tablePrefix, string $charsetCollate): string {
      $tableName = $this->tableName($tablePrefix);

      return "CREATE TABLE {$tableName} (
          id BIGINT NOT NULL AUTO_INCREMENT,
          name VARCHAR(255) NOT NULL,
          ownerId BIGINT UNSIGNED NOT NULL,
          logoUrl TINYTEXT NULL,
          isAcceptingRequests BOOLEAN NOT NULL DEFAULT true,
          PRIMARY KEY (id)
        ) {$charsetCollate};";
    }

    public function initialData(string $tablePrefix): void {
    }

    public function tableName(string $tablePrefix): string {
      return $tablePrefix . TableNames::TEAMS;
    }
  }

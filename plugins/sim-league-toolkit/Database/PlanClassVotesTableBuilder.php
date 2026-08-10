<?php

  namespace SLTK\Database;

  class PlanClassVotesTableBuilder extends TableBuilder {

    public function addConstraints(string $tablePrefix): void {
      $this->addSimpleForeignKey($tablePrefix, TableNames::PLAN_CLASSES, 'planClassId');
      $this->addSimpleForeignKey($tablePrefix, TableNames::USERS, 'userId');
    }

    public function applyAdjustments(string $tablePrefix): void {
    }

    public function definitionSql(string $tablePrefix, string $charsetCollate): string {
      $tableName = $this->tableName($tablePrefix);

      return "CREATE TABLE {$tableName} (
        planClassId bigint NOT NULL,
        userId bigint unsigned NOT NULL,
        PRIMARY KEY  (planClassId, userId)
      ) {$charsetCollate};";
    }

    public function initialData(string $tablePrefix): void {
    }

    public function tableName(string $tablePrefix): string {
      return $tablePrefix . TableNames::PLAN_CLASS_VOTES;
    }
  }

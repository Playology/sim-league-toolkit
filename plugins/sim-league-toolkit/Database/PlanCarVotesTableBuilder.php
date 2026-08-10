<?php

  namespace SLTK\Database;

  class PlanCarVotesTableBuilder extends TableBuilder {

    public function addConstraints(string $tablePrefix): void {
      $this->addSimpleForeignKey($tablePrefix, TableNames::CHAMPIONSHIP_PLANS, 'planId');
      $this->addSimpleForeignKey($tablePrefix, TableNames::CARS, 'carId');
      $this->addSimpleForeignKey($tablePrefix, TableNames::USERS, 'userId');
    }

    public function applyAdjustments(string $tablePrefix): void {
    }

    public function definitionSql(string $tablePrefix, string $charsetCollate): string {
      $tableName = $this->tableName($tablePrefix);

      return "CREATE TABLE {$tableName} (
        planId bigint NOT NULL,
        carId bigint NOT NULL,
        userId bigint unsigned NOT NULL,
        PRIMARY KEY  (planId, carId, userId)
      ) {$charsetCollate};";
    }

    public function initialData(string $tablePrefix): void {
    }

    public function tableName(string $tablePrefix): string {
      return $tablePrefix . TableNames::PLAN_CAR_VOTES;
    }
  }

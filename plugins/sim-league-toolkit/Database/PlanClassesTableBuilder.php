<?php

  namespace SLTK\Database;

  class PlanClassesTableBuilder extends TableBuilder {

    public function addConstraints(string $tablePrefix): void {
      $this->addSimpleForeignKey($tablePrefix, TableNames::CHAMPIONSHIP_PLANS, 'planId');
      $this->addSimpleForeignKey($tablePrefix, TableNames::EVENT_CLASSES, 'eventClassId');
      $this->addSimpleForeignKey($tablePrefix, TableNames::DRIVER_CATEGORIES, 'driverCategoryId');
      $this->addSimpleForeignKey($tablePrefix, TableNames::CARS, 'singleCarId');
      $this->addSimpleForeignKey($tablePrefix, TableNames::USERS, 'suggestedByUserId');
    }

    public function applyAdjustments(string $tablePrefix): void {
    }

    public function definitionSql(string $tablePrefix, string $charsetCollate): string {
      $tableName = $this->tableName($tablePrefix);

      return "CREATE TABLE {$tableName} (
        id bigint NOT NULL AUTO_INCREMENT,
        planId bigint NOT NULL,
        eventClassId bigint NULL,
        name tinytext NOT NULL,
        carClass tinytext NOT NULL,
        driverCategoryId bigint NULL,
        isSingleCarClass boolean NOT NULL DEFAULT 0,
        singleCarId bigint NULL,
        suggestedByUserId bigint unsigned NULL,
        PRIMARY KEY  (id)
      ) {$charsetCollate};";
    }

    public function initialData(string $tablePrefix): void {
    }

    public function tableName(string $tablePrefix): string {
      return $tablePrefix . TableNames::PLAN_CLASSES;
    }
  }

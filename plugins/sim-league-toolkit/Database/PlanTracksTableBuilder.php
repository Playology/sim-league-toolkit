<?php

  namespace SLTK\Database;

  class PlanTracksTableBuilder extends TableBuilder {

    public function addConstraints(string $tablePrefix): void {
      $this->addSimpleForeignKey($tablePrefix, TableNames::CHAMPIONSHIP_PLANS, 'planId');
      $this->addSimpleForeignKey($tablePrefix, TableNames::TRACKS, 'trackId');
    }

    public function applyAdjustments(string $tablePrefix): void {
    }

    public function definitionSql(string $tablePrefix, string $charsetCollate): string {
      $tableName = $this->tableName($tablePrefix);

      return "CREATE TABLE {$tableName} (
        planId bigint NOT NULL,
        trackId bigint NOT NULL,
        PRIMARY KEY  (planId, trackId)
      ) {$charsetCollate};";
    }

    public function initialData(string $tablePrefix): void {
    }

    public function tableName(string $tablePrefix): string {
      return $tablePrefix . TableNames::PLAN_TRACKS;
    }
  }

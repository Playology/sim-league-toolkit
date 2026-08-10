<?php

  namespace SLTK\Database;

  class ChampionshipPlansTableBuilder extends TableBuilder {

    public function addConstraints(string $tablePrefix): void {
      $this->addSimpleForeignKey($tablePrefix, TableNames::GAMES, 'gameId');
      $this->addSimpleForeignKey($tablePrefix, TableNames::PLATFORMS, 'platformId');
      $this->addSimpleForeignKey($tablePrefix, TableNames::CHAMPIONSHIPS, 'createdChampionshipId');
    }

    public function applyAdjustments(string $tablePrefix): void {
    }

    public function definitionSql(string $tablePrefix, string $charsetCollate): string {
      $tableName = $this->tableName($tablePrefix);

      return "CREATE TABLE {$tableName} (
        id bigint NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        description text NOT NULL,
        gameId bigint NOT NULL,
        platformId bigint NOT NULL,
        championshipType varchar(50) NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'draft',
        startDate date NOT NULL,
        classesFixed boolean NOT NULL DEFAULT 1,
        maxTrackVotesPerUser tinyint NOT NULL DEFAULT 1,
        maxCarVotesPerUser tinyint NOT NULL DEFAULT 1,
        maxCarsPerClass tinyint NOT NULL DEFAULT 1,
        createdChampionshipId bigint NULL,
        PRIMARY KEY  (id)
      ) {$charsetCollate};";
    }

    public function initialData(string $tablePrefix): void {
    }

    public function tableName(string $tablePrefix): string {
      return $tablePrefix . TableNames::CHAMPIONSHIP_PLANS;
    }
  }

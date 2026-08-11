<?php

  namespace SLTK\Database;

  class ProvisionedItemsTableBuilder extends TableBuilder {

    public function addConstraints(string $tablePrefix): void {
    }

    public function applyAdjustments(string $tablePrefix): void {
    }

    public function definitionSql(string $tablePrefix, string $charsetCollate): string {
      $tableName = $this->tableName($tablePrefix);

      return "CREATE TABLE {$tableName} (
            id BIGINT NOT NULL AUTO_INCREMENT,
            itemKey VARCHAR(50) NOT NULL,
            targetId BIGINT NOT NULL,
            kind VARCHAR(20) NOT NULL,
            contentHash VARCHAR(64) NOT NULL,
            provisionedVia VARCHAR(10) NOT NULL,
            provisionedAt DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_item_key (itemKey)
        ) {$charsetCollate};";
    }

    public function initialData(string $tablePrefix): void {
    }

    public function tableName(string $tablePrefix): string {
      return $tablePrefix . TableNames::PROVISIONED_ITEMS;
    }
  }

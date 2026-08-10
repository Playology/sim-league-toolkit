<?php

  namespace SLTK\Database;

  use SLTK\Core\Constants;

  class TrackLayoutsTableBuilder extends TableBuilder {

    public function addConstraints(string $tablePrefix): void {
      global $wpdb;

      $this->addSimpleForeignKey($tablePrefix, TableNames::GAMES, 'gameId');
      $this->addSimpleForeignKey($tablePrefix, TableNames::TRACKS, 'trackId');

      $tableName = $this->tableName($tablePrefix);
      $uniqueConstraintName = 'uq_track_layouts';
      $uniqueConstraintExistsCheckSql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
                        WHERE TABLE_NAME = '{$tableName}'
                        AND CONSTRAINT_NAME = '{$uniqueConstraintName}';";
      $uniqueConstraintExists = (int)$wpdb->get_var($uniqueConstraintExistsCheckSql);
      if (!$uniqueConstraintExists) {
        $uqSql = "ALTER TABLE {$tableName}
                    ADD CONSTRAINT {$uniqueConstraintName}
                        UNIQUE (gameId, trackId, layoutId(100));";
        $wpdb->query($uqSql);
      }
    }


    public function applyAdjustments(string $tablePrefix): void {
      global $wpdb;
      $tableName = $this->tableName($tablePrefix);

      // A signed tinyint's 127 max silently clamped ACC's Nürburgring 24h combined layout (170
      // corners) once it started seeding through this table - widen to smallint for headroom.
      $wpdb->query("ALTER TABLE {$tableName} MODIFY COLUMN corners smallint NOT NULL");
    }

    public function definitionSql(string $tablePrefix, string $charsetCollate): string {
      $tableName = $this->tableName($tablePrefix);

      return "CREATE TABLE {$tableName} (
          id bigint NOT NULL AUTO_INCREMENT,
          gameId bigint NOT NULL,
          trackId bigint NOT NULL,
          layoutId varchar(100) NOT NULL,
          name tinytext NOT NULL,
          corners smallint NOT NULL,
          length int NOT NULL,
          dlcPack tinytext NULL,
          PRIMARY KEY  (id)
        ) {$charsetCollate};";
    }

    public function initialData(string $tablePrefix): void {
      $this->loadLayouts($tablePrefix, 'ams2-track-layouts.csv', 'AMS2');
      $this->loadLayouts($tablePrefix, 'lmu-track-layouts.csv', 'LMU');
      $this->loadAccLayouts($tablePrefix);
    }

    /**
     * ACC has no real track/layout split (one configuration per venue), but a synthetic single
     * layout row per track lets corners/length/dlcPack reuse the exact same TrackLayouts-backed
     * queries as AMS2/LMU (e.g. the Championship Plan track filters), with no dual data-location
     * branching needed. Game::supportsLayouts stays false for ACC, so no UI ever shows a "pick your
     * one layout" dropdown - these rows are pure metadata carriers, never surfaced as a choice.
     */
    private function loadAccLayouts(string $tablePrefix): void {
      global $wpdb;
      $tableName = $this->tableName($tablePrefix);
      $gamesTableName = $tablePrefix . TableNames::GAMES;
      $tracksTableName = $tablePrefix . TableNames::TRACKS;

      $dataFilePath = Constants::PLUGIN_ROOT_DIR . 'data/acc-tracks.csv';

      $handle = fopen($dataFilePath, 'r');
      if ($handle !== false) {

        while (($data = fgetcsv($handle, 1000, ',', '"', '\\')) != false) {

          $trackKey = $data[0];
          $corners = isset($data[7]) && $data[7] !== '' ? (int)$data[7] : 0;
          $length = isset($data[8]) && $data[8] !== '' ? (int)$data[8] : 0;
          $dlcPack = !empty($data[9]) ? $data[9] : null;

          $gameId = $wpdb->get_var("SELECT id FROM {$gamesTableName} WHERE gameKey = 'ACC'");
          $trackId = $wpdb->get_var("SELECT id FROM {$tracksTableName} WHERE gameId = '{$gameId}' AND trackId = '{$trackKey}'");

          $this->upsertSeedRow($tableName, ['gameId' => $gameId, 'trackId' => $trackId, 'layoutId' => $trackKey], [
            'name' => 'Standard',
            'corners' => $corners,
            'length' => $length,
            'dlcPack' => $dlcPack,
          ]);
        }

        fclose($handle);
      }
    }

    public function loadLayouts(string $tablePrefix, string $fileName, string $gameKey): void {
      global $wpdb;
      $tableName = $this->tableName($tablePrefix);
      $gamesTableName = $tablePrefix . TableNames::GAMES;
      $tracksTableName = $tablePrefix . TableNames::TRACKS;

      $dataFilePath = Constants::PLUGIN_ROOT_DIR . 'data/' . $fileName;

      $handle = fopen($dataFilePath, 'r');
      if ($handle !== false) {

        while (($data = fgetcsv($handle, 1000, ',', '"', '\\')) != false) {

          $trackKey = $data[0];
          $layoutId = $data[1];
          $dlcPack = !empty($data[9]) ? $data[9] : null;

          $gameId = $wpdb->get_var("SELECT id FROM {$gamesTableName} WHERE gameKey = '{$gameKey}'");
          $trackId = $wpdb->get_var("SELECT id FROM {$tracksTableName} WHERE trackId = '{$trackKey}'");

          $this->upsertSeedRow($tableName, ['gameId' => $gameId, 'trackId' => $trackId, 'layoutId' => $layoutId], [
            'name' => $data[2],
            'corners' => $data[3],
            'length' => $data[4],
            'dlcPack' => $dlcPack,
          ]);
        }

        fclose($handle);
      }
    }

    public function tableName(string $tablePrefix): string {
      return $tablePrefix . TableNames::TRACK_LAYOUTS;
    }
  }
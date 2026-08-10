<?php

  namespace SLTK\Database;


  use SLTK\Core\Constants;

  class TracksTableBuilder extends TableBuilder {

    public function addConstraints(string $tablePrefix): void {
      $this->addSimpleForeignKey($tablePrefix, TableNames::GAMES, 'gameId');
    }

    public function applyAdjustments(string $tablePrefix): void {
    }

    public function definitionSql(string $tablePrefix, string $charsetCollate): string {
      $tableName = $this->tableName($tablePrefix);

      return "CREATE TABLE {$tableName} (
          id bigint NOT NULL AUTO_INCREMENT,
          gameId bigint NOT NULL,
          trackId tinytext NOT NULL,
          shortName tinytext NOT NULL,
          fullName mediumtext NOT NULL,
          country tinytext NOT NULL,
          countryCode tinytext NOT NULL,
          latitude double(10,6) NOT NULL,
          longitude double(10,6) NOT NULL,
          elevation int NULL,
          PRIMARY KEY  (id)
        ) {$charsetCollate};";
    }

    public function initialData(string $tablePrefix): void {
      $this->loadTracks($tablePrefix, 'acc-tracks.csv', 'ACC');
      $this->loadTracks($tablePrefix, 'ams2-tracks.csv', 'AMS2');
      $this->loadTracks($tablePrefix, 'lmu-tracks.csv', 'LMU');
    }

    public function tableName(string $tablePrefix): string {
      return $tablePrefix . TableNames::TRACKS;
    }

    private function loadTracks(string $tablePrefix, string $fileName, string $gameKey): void {
      global $wpdb;
      $tracksTableName = $this->tableName($tablePrefix);
      $gamesTableName = $tablePrefix . TableNames::GAMES;

      $dataFilePath = Constants::PLUGIN_ROOT_DIR . 'data/' . $fileName;

      $handle = fopen($dataFilePath, 'r');
      if ($handle !== false) {

        while (($data = fgetcsv($handle, 1000, ',', '"', '\\')) != false) {

          $trackId = $data[0];
          $elevation = ($gameKey === 'AMS2' && !empty($data[7])) ? $data[7] : null;

          $gameId = $wpdb->get_var("SELECT id FROM {$gamesTableName} WHERE gameKey = '$gameKey'");

          $this->upsertSeedRow($tracksTableName, ['gameId' => $gameId, 'trackId' => $trackId], [
            'shortName' => $data[1],
            'fullName' => $data[2],
            'country' => $data[3],
            'countryCode' => $data[4],
            'latitude' => $data[5],
            'longitude' => $data[6],
            'elevation' => $elevation,
          ]);
        }

        fclose($handle);
      }
    }
  }

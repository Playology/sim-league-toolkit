<?php

  namespace SLTK\Database\Repositories;

  use Exception;
  use SLTK\Database\TableNames;
  use stdClass;

  class EventTeamMembersRepository extends RepositoryBase {

    /**
     * @throws Exception
     */
    public static function add(array $data): int {
      return self::insert(TableNames::EVENT_TEAM_MEMBERS, $data);
    }

    /**
     * @throws Exception
     */
    public static function delete(int $id): void {
      self::deleteById(TableNames::EVENT_TEAM_MEMBERS, $id);
    }

    /**
     * @throws Exception
     */
    public static function deleteByEntry(string $entryScope, int $entryId): void {
      self::deleteFromTable(TableNames::EVENT_TEAM_MEMBERS, "entryScope = '{$entryScope}' AND entryId = {$entryId}");
    }

    /**
     * @return stdClass[]
     * @throws Exception
     */
    public static function listByEntry(string $entryScope, int $entryId): array {
      $eventTeamMembersTable = self::prefixedTableName(TableNames::EVENT_TEAM_MEMBERS);
      $usersTable = self::prefixedTableName(TableNames::USERS);

      $query = "SELECT
                  m.*,
                  u.display_name as memberName
                FROM {$eventTeamMembersTable} m
                LEFT JOIN {$usersTable} u ON m.memberId = u.ID
                WHERE m.entryScope = '{$entryScope}' AND m.entryId = '{$entryId}'
                ORDER BY u.display_name;";

      return self::getResults($query);
    }
  }

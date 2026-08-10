<?php

  namespace SLTK\Database\Repositories;

  use Exception;
  use SLTK\Database\TableNames;
  use stdClass;
  use Throwable;

  class TeamsRepository extends RepositoryBase {

    /**
     * @throws Exception
     */
    public static function add(array $data): int {
      return self::insert(TableNames::TEAMS, $data);
    }

    /**
     * @throws Throwable
     */
    public static function delete(int $id): void {
      self::transaction(function () use ($id) {
        self::deleteFromTable(TableNames::TEAM_MEMBERS, "teamId = {$id}");
        self::deleteFromTable(TableNames::TEAM_INVITATIONS, "teamId = {$id}");
        self::deleteFromTable(TableNames::TEAM_REQUESTS, "teamId = {$id}");
        self::deleteById(TableNames::TEAMS, $id);
      });
    }

    /**
     * @throws Exception
     */
    public static function update(int $id, array $data): void {
      self::updateById(TableNames::TEAMS, $id, $data);
    }

    /**
     * @throws Exception
     */
    public static function getById(int $id): ?stdClass {
      $teamsTable = self::prefixedTableName(TableNames::TEAMS);
      $usersTable = self::prefixedTableName(TableNames::USERS);
      $teamMembersTable = self::prefixedTableName(TableNames::TEAM_MEMBERS);

      $query = "SELECT
                  t.*,
                  u.display_name as ownerName,
                  (SELECT COUNT(*) FROM {$teamMembersTable} tm2 WHERE tm2.teamId = t.id) as memberCount
                FROM {$teamsTable} t
                LEFT JOIN {$usersTable} u ON t.ownerId = u.ID
                WHERE t.id = '{$id}';";

      return self::getRow($query);
    }

    /**
     * @return stdClass[]
     * @throws Exception
     */
    public static function list(): array {
      $teamsTable = self::prefixedTableName(TableNames::TEAMS);
      $usersTable = self::prefixedTableName(TableNames::USERS);
      $teamMembersTable = self::prefixedTableName(TableNames::TEAM_MEMBERS);

      $query = "SELECT
                  t.*,
                  u.display_name as ownerName,
                  (SELECT COUNT(*) FROM {$teamMembersTable} tm2 WHERE tm2.teamId = t.id) as memberCount
                FROM {$teamsTable} t
                LEFT JOIN {$usersTable} u ON t.ownerId = u.ID
                ORDER BY t.name;";

      return self::getResults($query);
    }

    /**
     * @return stdClass[]
     * @throws Exception
     */
    public static function listByMemberId(int $userId): array {
      $teamsTable = self::prefixedTableName(TableNames::TEAMS);
      $usersTable = self::prefixedTableName(TableNames::USERS);
      $teamMembersTable = self::prefixedTableName(TableNames::TEAM_MEMBERS);

      $query = "SELECT DISTINCT
                  t.*,
                  u.display_name as ownerName,
                  (SELECT COUNT(*) FROM {$teamMembersTable} tm2 WHERE tm2.teamId = t.id) as memberCount
                FROM {$teamsTable} t
                LEFT JOIN {$usersTable} u ON t.ownerId = u.ID
                LEFT JOIN {$teamMembersTable} tm ON tm.teamId = t.id
                WHERE t.ownerId = '{$userId}' OR tm.memberId = '{$userId}'
                ORDER BY t.name;";

      return self::getResults($query);
    }

    /**
     * @throws Exception
     */
    public static function isMember(int $teamId, int $memberId): bool {
      return self::simpleExists(TableNames::TEAM_MEMBERS, "teamId = {$teamId} AND memberId = {$memberId}");
    }

    /**
     * @throws Exception
     */
    public static function addMember(array $data): int {
      return self::insert(TableNames::TEAM_MEMBERS, $data);
    }

    /**
     * @throws Exception
     */
    public static function deleteMember(int $id): void {
      self::deleteById(TableNames::TEAM_MEMBERS, $id);
    }

    /**
     * @throws Exception
     */
    public static function deleteMemberByTeamAndMemberId(int $teamId, int $memberId): void {
      self::deleteFromTable(TableNames::TEAM_MEMBERS, "teamId = {$teamId} AND memberId = {$memberId}");
    }

    /**
     * @return stdClass[]
     * @throws Exception
     */
    public static function listMembersByTeamId(int $teamId): array {
      $teamMembersTable = self::prefixedTableName(TableNames::TEAM_MEMBERS);
      $usersTable = self::prefixedTableName(TableNames::USERS);

      $query = "SELECT
                  tm.*,
                  u.display_name as memberName
                FROM {$teamMembersTable} tm
                LEFT JOIN {$usersTable} u ON tm.memberId = u.ID
                WHERE tm.teamId = '{$teamId}'
                ORDER BY u.display_name;";

      return self::getResults($query);
    }

    /**
     * @throws Exception
     */
    public static function addInvitation(array $data): int {
      return self::insert(TableNames::TEAM_INVITATIONS, $data);
    }

    /**
     * @throws Exception
     */
    public static function updateInvitation(int $id, array $data): void {
      self::updateById(TableNames::TEAM_INVITATIONS, $id, $data);
    }

    /**
     * @throws Exception
     */
    public static function deleteInvitation(int $id): void {
      self::deleteById(TableNames::TEAM_INVITATIONS, $id);
    }

    /**
     * @throws Exception
     */
    public static function getInvitationById(int $id): ?stdClass {
      return self::getRowFromInvitationsWithNames("i.id = '{$id}'");
    }

    /**
     * @return stdClass[]
     * @throws Exception
     */
    public static function listPendingInvitationsByTeamId(int $teamId): array {
      return self::getResultsFromInvitationsWithNames("i.teamId = '{$teamId}' AND i.pending = 1");
    }

    /**
     * @return stdClass[]
     * @throws Exception
     */
    public static function listPendingInvitationsByMemberId(int $memberId): array {
      return self::getResultsFromInvitationsWithNames("i.memberId = '{$memberId}' AND i.pending = 1");
    }

    /**
     * @throws Exception
     */
    public static function addRequest(array $data): int {
      return self::insert(TableNames::TEAM_REQUESTS, $data);
    }

    /**
     * @throws Exception
     */
    public static function updateRequest(int $id, array $data): void {
      self::updateById(TableNames::TEAM_REQUESTS, $id, $data);
    }

    /**
     * @throws Exception
     */
    public static function deleteRequest(int $id): void {
      self::deleteById(TableNames::TEAM_REQUESTS, $id);
    }

    /**
     * @throws Exception
     */
    public static function getRequestById(int $id): ?stdClass {
      return self::getRowFromRequestsWithNames("r.id = '{$id}'");
    }

    /**
     * @return stdClass[]
     * @throws Exception
     */
    public static function listPendingRequestsByTeamId(int $teamId): array {
      return self::getResultsFromRequestsWithNames("r.teamId = '{$teamId}' AND r.pending = 1");
    }

    /**
     * @return stdClass[]
     * @throws Exception
     */
    public static function listPendingRequestsByMemberId(int $memberId): array {
      return self::getResultsFromRequestsWithNames("r.memberId = '{$memberId}' AND r.pending = 1");
    }

    /**
     * @throws Exception
     */
    private static function getResultsFromInvitationsWithNames(string $filter): array {
      $invitationsTable = self::prefixedTableName(TableNames::TEAM_INVITATIONS);
      $usersTable = self::prefixedTableName(TableNames::USERS);
      $teamsTable = self::prefixedTableName(TableNames::TEAMS);

      $query = "SELECT
                  i.*,
                  u.display_name as memberName,
                  t.name as teamName
                FROM {$invitationsTable} i
                LEFT JOIN {$usersTable} u ON i.memberId = u.ID
                LEFT JOIN {$teamsTable} t ON i.teamId = t.id
                WHERE {$filter}
                ORDER BY i.id DESC;";

      return self::getResults($query);
    }

    /**
     * @throws Exception
     */
    private static function getRowFromInvitationsWithNames(string $filter): ?stdClass {
      $results = self::getResultsFromInvitationsWithNames($filter);

      return $results[0] ?? null;
    }

    /**
     * @throws Exception
     */
    private static function getResultsFromRequestsWithNames(string $filter): array {
      $requestsTable = self::prefixedTableName(TableNames::TEAM_REQUESTS);
      $usersTable = self::prefixedTableName(TableNames::USERS);
      $teamsTable = self::prefixedTableName(TableNames::TEAMS);

      $query = "SELECT
                  r.*,
                  u.display_name as memberName,
                  t.name as teamName
                FROM {$requestsTable} r
                LEFT JOIN {$usersTable} u ON r.memberId = u.ID
                LEFT JOIN {$teamsTable} t ON r.teamId = t.id
                WHERE {$filter}
                ORDER BY r.id DESC;";

      return self::getResults($query);
    }

    /**
     * @throws Exception
     */
    private static function getRowFromRequestsWithNames(string $filter): ?stdClass {
      $results = self::getResultsFromRequestsWithNames($filter);

      return $results[0] ?? null;
    }
  }

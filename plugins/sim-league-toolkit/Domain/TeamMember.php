<?php

  namespace SLTK\Domain;

  use Exception;
  use SLTK\Core\Constants;
  use SLTK\Database\Repositories\TeamsRepository;
  use SLTK\Domain\Abstractions\Deletable;
  use SLTK\Domain\Abstractions\ProvidesPersistableArray;
  use SLTK\Domain\Traits\HasIdentity;
  use stdClass;

  class TeamMember implements Deletable, ProvidesPersistableArray {
    use HasIdentity;

    private string $memberName = '';
    private int $memberId = Constants::DEFAULT_ID;
    private int $teamId = Constants::DEFAULT_ID;

    /**
     * @throws Exception
     */
    public static function add(int $teamId, int $memberId): self {
      $result = new self();
      $result->setTeamId($teamId);
      $result->setMemberId($memberId);
      $result->setId(TeamsRepository::addMember($result->toArray()));

      return $result;
    }

    /**
     * @throws Exception
     */
    public static function delete(int $id): void {
      TeamsRepository::deleteMember($id);
    }

    /**
     * @throws Exception
     */
    public static function deleteByTeamAndMemberId(int $teamId, int $memberId): void {
      TeamsRepository::deleteMemberByTeamAndMemberId($teamId, $memberId);
    }

    public static function fromStdClass(?stdClass $data): ?self {
      if (!$data) {
        return null;
      }

      $result = new self();

      $result->setId((int)$data->id);
      $result->setTeamId((int)$data->teamId);
      $result->setMemberId((int)$data->memberId);
      $result->setMemberName($data->memberName ?? '');

      return $result;
    }

    /**
     * @throws Exception
     */
    public static function isMember(int $teamId, int $userId): bool {
      return TeamsRepository::isMember($teamId, $userId);
    }

    /**
     * @return self[]
     * @throws Exception
     */
    public static function listByTeamId(int $teamId): array {
      return array_map(fn($row) => self::fromStdClass($row), TeamsRepository::listMembersByTeamId($teamId));
    }

    public function getMemberId(): int {
      return $this->memberId;
    }

    public function setMemberId(int $value): void {
      $this->memberId = $value;
    }

    public function getMemberName(): string {
      return $this->memberName;
    }

    private function setMemberName(string $value): void {
      $this->memberName = $value;
    }

    public function getTeamId(): int {
      return $this->teamId;
    }

    public function setTeamId(int $value): void {
      $this->teamId = $value;
    }

    public function toArray(): array {
      return [
        'teamId' => $this->getTeamId(),
        'memberId' => $this->getMemberId(),
      ];
    }

    public function toDto(): array {
      return [
        'id' => $this->getId(),
        'teamId' => $this->getTeamId(),
        'memberId' => $this->getMemberId(),
        'memberName' => $this->getMemberName(),
      ];
    }
  }

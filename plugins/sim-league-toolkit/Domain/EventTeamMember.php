<?php

  namespace SLTK\Domain;

  use Exception;
  use SLTK\Core\Constants;
  use SLTK\Core\Enums\EventEntryScope;
  use SLTK\Database\Repositories\EventTeamMembersRepository;
  use SLTK\Domain\Abstractions\Deletable;
  use SLTK\Domain\Abstractions\ProvidesPersistableArray;
  use SLTK\Domain\Traits\HasIdentity;
  use stdClass;

  class EventTeamMember implements Deletable, ProvidesPersistableArray {
    use HasIdentity;

    private EventEntryScope $entryScope;
    private int $entryId = Constants::DEFAULT_ID;
    private int $memberId = Constants::DEFAULT_ID;
    private string $memberName = '';

    /**
     * @throws Exception
     */
    public static function add(EventEntryScope $entryScope, int $entryId, int $memberId): self {
      $result = new self();
      $result->entryScope = $entryScope;
      $result->setEntryId($entryId);
      $result->setMemberId($memberId);
      $result->setId(EventTeamMembersRepository::add($result->toArray()));

      return $result;
    }

    /**
     * @throws Exception
     */
    public static function delete(int $id): void {
      EventTeamMembersRepository::delete($id);
    }

    /**
     * @throws Exception
     */
    public static function deleteByEntry(EventEntryScope $entryScope, int $entryId): void {
      EventTeamMembersRepository::deleteByEntry($entryScope->value, $entryId);
    }

    public static function fromStdClass(?stdClass $data): ?self {
      if (!$data) {
        return null;
      }

      $result = new self();

      $result->setId((int)$data->id);
      $result->entryScope = EventEntryScope::from($data->entryScope);
      $result->setEntryId((int)$data->entryId);
      $result->setMemberId((int)$data->memberId);
      $result->setMemberName($data->memberName ?? '');

      return $result;
    }

    /**
     * @return self[]
     * @throws Exception
     */
    public static function listByEntry(EventEntryScope $entryScope, int $entryId): array {
      return array_map(
        fn($row) => self::fromStdClass($row),
        EventTeamMembersRepository::listByEntry($entryScope->value, $entryId)
      );
    }

    public function getEntryId(): int {
      return $this->entryId;
    }

    public function setEntryId(int $value): void {
      $this->entryId = $value;
    }

    public function getEntryScope(): EventEntryScope {
      return $this->entryScope;
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

    public function toArray(): array {
      return [
        'entryScope' => $this->getEntryScope()->value,
        'entryId' => $this->getEntryId(),
        'memberId' => $this->getMemberId(),
      ];
    }

    public function toDto(): array {
      return [
        'id' => $this->getId(),
        'entryScope' => $this->getEntryScope()->value,
        'entryId' => $this->getEntryId(),
        'memberId' => $this->getMemberId(),
        'memberName' => $this->getMemberName(),
      ];
    }
  }

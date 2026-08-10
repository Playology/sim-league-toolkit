<?php

  namespace SLTK\Domain;

  use Exception;
  use SLTK\Core\Constants;
  use SLTK\Database\Repositories\TeamsRepository;
  use SLTK\Domain\Abstractions\AggregateRoot;
  use SLTK\Domain\Abstractions\Deletable;
  use SLTK\Domain\Abstractions\Listable;
  use SLTK\Domain\Abstractions\ProvidesPersistableArray;
  use SLTK\Domain\Abstractions\Saveable;
  use SLTK\Domain\Traits\HasIdentity;
  use stdClass;
  use Throwable;

  class Team implements AggregateRoot, Deletable, Listable, ProvidesPersistableArray, Saveable {
    use HasIdentity;

    private bool $isAcceptingRequests = true;
    private string $logoUrl = '';
    private int $memberCount = 0;
    private string $name = '';
    private int $ownerId = Constants::DEFAULT_ID;
    private string $ownerName = '';

    /**
     * @throws Throwable
     */
    public static function delete(int $id): void {
      TeamsRepository::delete($id);
    }

    public static function fromStdClass(?stdClass $data): ?self {
      if (!$data) {
        return null;
      }

      $result = new self();

      $result->setId((int)$data->id);
      $result->setName($data->name ?? '');
      $result->setOwnerId((int)$data->ownerId);
      $result->setOwnerName($data->ownerName ?? '');
      $result->setLogoUrl($data->logoUrl ?? '');
      $result->setIsAcceptingRequests((bool)($data->isAcceptingRequests ?? true));
      $result->setMemberCount((int)($data->memberCount ?? 0));

      return $result;
    }

    /**
     * @throws Exception
     */
    public static function get(int $id): ?self {
      return self::fromStdClass(TeamsRepository::getById($id));
    }

    /**
     * @return self[]
     * @throws Exception
     */
    public static function list(): array {
      return array_map(fn($row) => self::fromStdClass($row), TeamsRepository::list());
    }

    /**
     * @return self[]
     * @throws Exception
     */
    public static function listByMemberId(int $userId): array {
      return array_map(fn($row) => self::fromStdClass($row), TeamsRepository::listByMemberId($userId));
    }

    public function getIsAcceptingRequests(): bool {
      return $this->isAcceptingRequests;
    }

    public function setIsAcceptingRequests(bool $value): void {
      $this->isAcceptingRequests = $value;
    }

    public function getLogoUrl(): string {
      return $this->logoUrl;
    }

    public function setLogoUrl(string $value): void {
      $this->logoUrl = $value;
    }

    public function getMemberCount(): int {
      return $this->memberCount;
    }

    private function setMemberCount(int $value): void {
      $this->memberCount = $value;
    }

    public function getName(): string {
      return $this->name;
    }

    public function setName(string $value): void {
      $this->name = $value;
    }

    public function getOwnerId(): int {
      return $this->ownerId;
    }

    public function setOwnerId(int $value): void {
      $this->ownerId = $value;
    }

    public function getOwnerName(): string {
      return $this->ownerName;
    }

    private function setOwnerName(string $value): void {
      $this->ownerName = $value;
    }

    public function isOwnedBy(int $userId): bool {
      return $this->ownerId === $userId;
    }

    /**
     * @throws Exception
     */
    public function save(): self {
      if (!$this->hasId()) {
        $this->setId(TeamsRepository::add($this->toArray()));
      } else {
        TeamsRepository::update($this->getId(), $this->toArray());
      }

      return $this;
    }

    public function toArray(): array {
      return [
        'name' => $this->getName(),
        'ownerId' => $this->getOwnerId(),
        'logoUrl' => $this->getLogoUrl(),
        'isAcceptingRequests' => $this->getIsAcceptingRequests(),
      ];
    }

    public function toDto(): array {
      return [
        'id' => $this->getId(),
        'name' => $this->getName(),
        'ownerId' => $this->getOwnerId(),
        'ownerName' => $this->getOwnerName(),
        'logoUrl' => $this->getLogoUrl(),
        'isAcceptingRequests' => $this->getIsAcceptingRequests(),
        'memberCount' => $this->getMemberCount(),
      ];
    }
  }

<?php

  namespace SLTK\Domain;

  use Exception;
  use SLTK\Core\Constants;
  use SLTK\Database\Repositories\TeamsRepository;
  use SLTK\Domain\Abstractions\AggregateRoot;
  use SLTK\Domain\Abstractions\Deletable;
  use SLTK\Domain\Abstractions\ProvidesPersistableArray;
  use SLTK\Domain\Abstractions\Saveable;
  use SLTK\Domain\Traits\HasIdentity;
  use stdClass;

  class TeamInvitation implements AggregateRoot, Deletable, ProvidesPersistableArray, Saveable {
    use HasIdentity;

    private bool $accepted = false;
    private int $memberId = Constants::DEFAULT_ID;
    private string $memberName = '';
    private bool $pending = true;
    private bool $rejected = false;
    private int $teamId = Constants::DEFAULT_ID;
    private string $teamName = '';

    /**
     * @throws Exception
     */
    public static function create(int $teamId, int $memberId): self {
      $result = new self();
      $result->setTeamId($teamId);
      $result->setMemberId($memberId);

      return $result->save();
    }

    /**
     * @throws Exception
     */
    public static function delete(int $id): void {
      TeamsRepository::deleteInvitation($id);
    }

    public static function fromStdClass(?stdClass $data): ?self {
      if (!$data) {
        return null;
      }

      $result = new self();

      $result->setId((int)$data->id);
      $result->setTeamId((int)$data->teamId);
      $result->setTeamName($data->teamName ?? '');
      $result->setMemberId((int)$data->memberId);
      $result->setMemberName($data->memberName ?? '');
      $result->setPending((bool)$data->pending);
      $result->setRejected((bool)$data->rejected);
      $result->setAccepted((bool)$data->accepted);

      return $result;
    }

    /**
     * @throws Exception
     */
    public static function get(int $id): ?self {
      return self::fromStdClass(TeamsRepository::getInvitationById($id));
    }

    /**
     * @return self[]
     * @throws Exception
     */
    public static function listPendingByMemberId(int $memberId): array {
      return array_map(fn($row) => self::fromStdClass($row), TeamsRepository::listPendingInvitationsByMemberId($memberId));
    }

    /**
     * @return self[]
     * @throws Exception
     */
    public static function listPendingByTeamId(int $teamId): array {
      return array_map(fn($row) => self::fromStdClass($row), TeamsRepository::listPendingInvitationsByTeamId($teamId));
    }

    /**
     * @throws Exception
     */
    public function accept(): void {
      $this->setPending(false);
      $this->setAccepted(true);
      $this->save();

      TeamMember::add($this->getTeamId(), $this->getMemberId());
    }

    public function getAccepted(): bool {
      return $this->accepted;
    }

    private function setAccepted(bool $value): void {
      $this->accepted = $value;
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

    public function getPending(): bool {
      return $this->pending;
    }

    private function setPending(bool $value): void {
      $this->pending = $value;
    }

    public function getRejected(): bool {
      return $this->rejected;
    }

    private function setRejected(bool $value): void {
      $this->rejected = $value;
    }

    public function getTeamId(): int {
      return $this->teamId;
    }

    public function setTeamId(int $value): void {
      $this->teamId = $value;
    }

    public function getTeamName(): string {
      return $this->teamName;
    }

    private function setTeamName(string $value): void {
      $this->teamName = $value;
    }

    /**
     * @throws Exception
     */
    public function reject(): void {
      $this->setPending(false);
      $this->setRejected(true);
      $this->save();
    }

    /**
     * @throws Exception
     */
    public function save(): self {
      if (!$this->hasId()) {
        $this->setId(TeamsRepository::addInvitation($this->toArray()));
      } else {
        TeamsRepository::updateInvitation($this->getId(), $this->toArray());
      }

      return $this;
    }

    public function toArray(): array {
      return [
        'teamId' => $this->getTeamId(),
        'memberId' => $this->getMemberId(),
        'pending' => $this->getPending(),
        'rejected' => $this->getRejected(),
        'accepted' => $this->getAccepted(),
      ];
    }

    public function toDto(): array {
      return [
        'id' => $this->getId(),
        'teamId' => $this->getTeamId(),
        'teamName' => $this->getTeamName(),
        'memberId' => $this->getMemberId(),
        'memberName' => $this->getMemberName(),
        'pending' => $this->getPending(),
        'rejected' => $this->getRejected(),
        'accepted' => $this->getAccepted(),
      ];
    }
  }

<?php

  namespace SLTK\Domain\Traits;

  use SLTK\Core\Constants;
  use stdClass;

  trait HasEntrantFields {
    private string $avatarUrl = '';
    private int $carId = Constants::DEFAULT_ID;
    private string $carName = '';
    private string $createdAt = '';
    private string $firstName = '';
    private string $lastName = '';
    private string $memberName = '';
    private int $raceNumber = 0;
    private string $status = 'confirmed';
    private ?string $teamName = null;
    private int $userId = Constants::DEFAULT_ID;

    public function getAvatarUrl(): string {
      return $this->avatarUrl;
    }

    private function setAvatarUrl(string $value): void {
      $this->avatarUrl = $value;
    }

    public function getCarId(): int {
      return $this->carId;
    }

    public function setCarId(int $value): void {
      $this->carId = $value;
    }

    public function getCarName(): string {
      return $this->carName;
    }

    private function setCarName(string $value): void {
      $this->carName = $value;
    }

    public function getCreatedAt(): string {
      return $this->createdAt;
    }

    private function setCreatedAt(string $value): void {
      $this->createdAt = $value;
    }

    public function getFirstName(): string {
      return $this->firstName;
    }

    private function setFirstName(string $value): void {
      $this->firstName = $value;
    }

    public function getLastName(): string {
      return $this->lastName;
    }

    private function setLastName(string $value): void {
      $this->lastName = $value;
    }

    public function getMemberName(): string {
      return $this->memberName;
    }

    private function setMemberName(string $value): void {
      $this->memberName = $value;
    }

    public function getRaceNumber(): int {
      return $this->raceNumber;
    }

    private function setRaceNumber(int $value): void {
      $this->raceNumber = $value;
    }

    public function getStatus(): string {
      return $this->status;
    }

    public function setStatus(string $value): void {
      $this->status = $value;
    }

    public function getTeamName(): ?string {
      return $this->teamName;
    }

    public function setTeamName(?string $value): void {
      $this->teamName = $value;
    }

    public function isTeamEntry(): bool {
      return $this->teamName !== null;
    }

    public function getUserId(): int {
      return $this->userId;
    }

    public function setUserId(int $value): void {
      $this->userId = $value;
    }

    private function entrantFieldsToArray(): array {
      return [
        'carId' => $this->getCarId(),
        'userId' => $this->getUserId(),
        'status' => $this->getStatus(),
        'teamName' => $this->getTeamName(),
      ];
    }

    private function entrantFieldsToDto(): array {
      return [
        'carId' => $this->getCarId(),
        'userId' => $this->getUserId(),
        'memberName' => $this->getMemberName(),
        'firstName' => $this->getFirstName(),
        'lastName' => $this->getLastName(),
        'raceNumber' => $this->getRaceNumber(),
        'avatarUrl' => $this->getAvatarUrl(),
        'carName' => $this->getCarName(),
        'status' => $this->getStatus(),
        'createdAt' => $this->getCreatedAt(),
        'teamName' => $this->getTeamName(),
      ];
    }

    private function hydrateEntrantFieldsFromStdClass(stdClass $data): void {
      $this->setCarId((int)$data->carId);
      $this->setUserId((int)$data->userId);
      $this->setMemberName($data->memberName ?? '');
      $this->setFirstName($data->firstName ?? '');
      $this->setLastName($data->lastName ?? '');
      $this->setRaceNumber((int)($data->raceNumber ?? 0));
      $this->setAvatarUrl(get_avatar_url((int)$data->userId) ?: '');
      $this->setCarName($data->carName ?? '');
      $this->setStatus($data->status ?? 'confirmed');
      $this->setCreatedAt($data->created_at ?? '');
      $this->setTeamName($data->teamName ?? null);
    }
  }

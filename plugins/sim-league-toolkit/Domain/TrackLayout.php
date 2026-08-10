<?php

  namespace SLTK\Domain;

  use SLTK\Core\Constants;
  use SLTK\Domain\Abstractions\ValueObject;
  use SLTK\Domain\Traits\HasIdentity;
  use stdClass;

  class TrackLayout implements ValueObject {
    use HasIdentity;

    private int $corners;
    private ?string $dlcPack = null;
    private string $game;
    private int $gameId;
    private string $layoutId;
    private int $length;
    private string $name;
    private string $track;
    private int $trackId;

    public static function fromStdClass(?stdClass $data): ?self {

      if (!$data) {
        return null;
      }

      $result = new self();

      $result->setId($data->id);
      $result->seCorners($data->corners);
      $result->setDlcPack($data->dlcPack ?? null);
      $result->setGame($data->game);
      $result->setGameId($data->gameId);
      $result->setLayoutId($data->layoutId);
      $result->setLength($data->length);
      $result->setName($data->name);
      $result->setTrack($data->track);
      $result->setTrackId($data->trackId);

      return $result;
    }

    public function getCorners(): int {
      return $this->corners ?? 0;
    }

    public function getDlcPack(): ?string {
      return $this->dlcPack ?? null;
    }

    public function getGame(): string {
      return $this->game ?? '';
    }

    public function getGameId(): int {
      return $this->gameId ?? Constants::DEFAULT_ID;
    }

    public function getLayoutId(): string {
      return $this->layoutId;
    }

    public function getLength(): int {
      return $this->length ?? 0;
    }

    public function getName(): string {
      return $this->name ?? '';
    }

    public function getTrack(): string {
      return $this->track ?? '';
    }

    public function getTrackId(): int {
      return $this->trackId ?? Constants::DEFAULT_ID;
    }

    public function toDto(): array {
      return [
        'id' => $this->getId(),
        'corners' => $this->getCorners(),
        'dlcPack' => $this->getDlcPack(),
        'game' => $this->getGame(),
        'gameId' => $this->getGameId(),
        'layoutId' => $this->getLayoutId(),
        'length' => $this->getLength(),
        'name' => $this->getName(),
        'track' => $this->getTrack(),
        'trackId' => $this->getTrackId(),
      ];
    }

    private function seCorners(int $corners): void {
      $this->corners = $corners;
    }

    private function setDlcPack(?string $dlcPack): void {
      $this->dlcPack = $dlcPack;
    }

    private function setGame(string $game): void {
      $this->game = $game;
    }

    private function setGameId(int $gameId): void {
      $this->gameId = $gameId;
    }

    private function setLayoutId(string $layoutId): void {
      $this->layoutId = $layoutId;
    }

    private function setLength(int $length): void {
      $this->length = $length;
    }

    private function setName(string $name): void {
      $this->name = $name;
    }

    private function setTrack(string $track): void {
      $this->track = $track;
    }

    private function setTrackId(int $trackId): void {
      $this->trackId = $trackId;
    }
  }
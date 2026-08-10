<?php

  namespace SLTK\Domain;

  use DateMalformedStringException;
  use DateTime;
  use DateTimeInterface;
  use DateTimeZone;
  use Exception;
  use SLTK\Core\Constants;
  use SLTK\Database\Repositories\ChampionshipEventsRepository;
  use SLTK\Database\Repositories\EventRefsRepository;
  use SLTK\Database\Repositories\EventSessionRepository;
  use SLTK\Domain\Abstractions\AggregateRoot;
  use SLTK\Domain\Abstractions\Deletable;
  use SLTK\Domain\Abstractions\ProvidesPersistableArray;
  use SLTK\Domain\Abstractions\Saveable;
  use SLTK\Domain\Traits\HasIdentity;
  use stdClass;

  class ChampionshipEvent implements AggregateRoot, Deletable, ProvidesPersistableArray, Saveable {
    use HasIdentity;

    private string $bannerImageUrl = '';
    private string $championship = '';
    private int $championshipId = Constants::DEFAULT_ID;
    private ?int $eventRefId = null;
    private bool $isActive = false;
    private bool $isCompleted = false;
    private string $name = '';
    private DateTime $startDateTime;
    private string $track = '';
    private int $trackId = Constants::DEFAULT_ID;
    private ?string $trackLayout = null;
    private ?int $trackLayoutId = null;
    private ?string $trackMasterCar = null;
    private ?int $trackMasterCarId = null;
    private bool $trophiesAwarded = false;

    /**
     * @throws DateMalformedStringException
     */
    public function __construct() {
      $this->startDateTime = new DateTime('now', new DateTimeZone('UTC'));
    }

    /**
     * @throws Exception
     */
    public static function delete(int $id): void {
      $event = self::get($id);

      if ($event === null) {
        return;
      }

      if ($event->hasEventRef()) {
        EventRefsRepository::delete($event->getEventRefId());
      }

      ChampionshipEventsRepository::delete($id);
    }

    public static function fromStdClass(?stdClass $data): ?self {
      if (!$data) {
        return null;
      }

      $result = new self();
      $result->setId((int)$data->id);
      $result->setEventRefId(isset($data->eventRefId) ? (int)$data->eventRefId : null);
      $result->setChampionshipId((int)$data->championshipId);
      $result->setTrackId((int)$data->trackId);
      $result->setTrackLayoutId(isset($data->trackLayoutId) ? (int)$data->trackLayoutId : null);
      $result->setName($data->name ?? '');
      $startDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $data->startDateTime, new DateTimeZone('UTC'));
      $result->setStartDateTime($startDateTime ?: new DateTime('now', new DateTimeZone('UTC')));
      $result->setIsActive((bool)($data->isActive ?? false));
      $result->setIsCompleted((bool)($data->isCompleted ?? false));
      $result->setBannerImageUrl($data->bannerImageUrl ?? '');
      $result->setChampionship($data->championship ?? '');
      $result->setTrack($data->track ?? '');
      $result->setTrackLayout($data->trackLayout ?? null);
      $result->setTrackMasterCarId(isset($data->trackMasterCarId) ? (int)$data->trackMasterCarId : null);
      $result->setTrackMasterCar($data->trackMasterCar ?? null);
      $result->setTrophiesAwarded((bool)($data->trophiesAwarded ?? false));

      return $result;
    }

    /**
     * @throws Exception
     */
    public static function get(int $id): ?self {
      $row = ChampionshipEventsRepository::getById($id);

      return self::fromStdClass($row);
    }

    public function getBannerImageUrl(): string {
      return $this->bannerImageUrl;
    }

    public function setBannerImageUrl(string $value): void {
      $this->bannerImageUrl = $value;
    }

    public function getChampionship(): string {
      return $this->championship;
    }

    private function setChampionship(string $value): void {
      $this->championship = $value;
    }

    public function getChampionshipId(): int {
      return $this->championshipId;
    }

    public function setChampionshipId(int $value): void {
      $this->championshipId = $value;
    }

    public function getEventRefId(): ?int {
      return $this->eventRefId;
    }

    public function setEventRefId(?int $value): void {
      $this->eventRefId = $value;
    }

    public function getIsActive(): bool {
      return $this->isActive;
    }

    public function setIsActive(bool $value): void {
      $this->isActive = $value;
    }

    public function getIsCompleted(): bool {
      return $this->isCompleted;
    }

    public function setIsCompleted(bool $value): void {
      $this->isCompleted = $value;
    }

    public function getName(): string {
      return $this->name;
    }

    public function setName(string $value): void {
      $this->name = trim($value);
    }

    public function getStartDateTime(): DateTime {
      return $this->startDateTime;
    }

    public function setStartDateTime(DateTime $value): void {
      $this->startDateTime = $value;
    }

    public function getTrack(): string {
      return $this->track;
    }

    private function setTrack(string $value): void {
      $this->track = $value;
    }

    public function getTrackId(): int {
      return $this->trackId;
    }

    public function setTrackId(int $value): void {
      $this->trackId = $value;
    }

    public function getTrackLayout(): ?string {
      return $this->trackLayout;
    }

    private function setTrackLayout(?string $value): void {
      $this->trackLayout = $value;
    }

    public function getTrackLayoutId(): ?int {
      return $this->trackLayoutId;
    }

    public function setTrackLayoutId(?int $value): void {
      $this->trackLayoutId = $value;
    }

    public function getTrackMasterCar(): ?string {
      return $this->trackMasterCar;
    }

    private function setTrackMasterCar(?string $value): void {
      $this->trackMasterCar = $value;
    }

    public function getTrackMasterCarId(): ?int {
      return $this->trackMasterCarId;
    }

    public function setTrackMasterCarId(?int $value): void {
      $this->trackMasterCarId = $value;
    }

    public function getTrophiesAwarded(): bool {
      return $this->trophiesAwarded;
    }

    public function setTrophiesAwarded(bool $value): void {
      $this->trophiesAwarded = $value;
    }

    /**
     * @return EventSession[]
     * @throws Exception
     */
    public static function listSessions(int $eventRefId): array {
      $results = EventSessionRepository::listByEventRefId($eventRefId);

      return array_map(fn($row) => EventSession::fromStdClass($row), $results);
    }

    /**
     * @throws Exception
     */
    public static function getSessionById(int $id): ?EventSession {
      return EventSession::fromStdClass(EventSessionRepository::getById($id));
    }

    /**
     * @throws Exception
     */
    public static function deleteSession(int $id): void {
      EventSessionRepository::delete($id);
    }

    /**
     * @throws Exception
     */
    public static function reorderSessions(int $eventRefId, array $sessionIds): void {
      foreach ($sessionIds as $sortOrder => $id) {
        EventSessionRepository::updateSortOrder((int)$id, $sortOrder);
      }
    }

    /**
     * @throws Exception
     */
    public static function saveSession(EventSession $session): bool {
      try {
        if ($session->hasId()) {
          EventSessionRepository::update($session->getId(), $session->toArray());
        } else {
          $session->setId(EventSessionRepository::add($session->toArray()));
        }

        return true;
      } catch (Exception) {
        return false;
      }
    }

    public function hasEventRef(): bool {
      return $this->eventRefId !== null && $this->eventRefId !== Constants::DEFAULT_ID;
    }

    /**
     * @throws Exception
     */
    public function save(): self {
      if (!$this->hasId()) {
        $eventRefId = EventRefsRepository::add('championship');
        $this->setEventRefId($eventRefId);
        $this->setId(ChampionshipEventsRepository::add($this->toArray()));
      } else {
        ChampionshipEventsRepository::update($this->getId(), $this->toArray());
      }

      return $this;
    }

    public function toArray(): array {
      return [
        'eventRefId' => $this->getEventRefId(),
        'championshipId' => $this->getChampionshipId(),
        'trackId' => $this->getTrackId(),
        'trackLayoutId' => $this->getTrackLayoutId(),
        'trackMasterCarId' => $this->getTrackMasterCarId(),
        'name' => $this->getName(),
        'startDateTime' => $this->getStartDateTime()->format('Y-m-d H:i:s'),
        'isActive' => $this->getIsActive(),
        'isCompleted' => $this->getIsCompleted(),
        'bannerImageUrl' => $this->getBannerImageUrl(),
        'trophiesAwarded' => $this->getTrophiesAwarded(),
      ];
    }

    public function toDto(): array {
      return [
        'id' => $this->getId(),
        'eventRefId' => $this->getEventRefId(),
        'championshipId' => $this->getChampionshipId(),
        'trackId' => $this->getTrackId(),
        'trackLayoutId' => $this->getTrackLayoutId(),
        'trackMasterCarId' => $this->getTrackMasterCarId(),
        'trackMasterCar' => $this->getTrackMasterCar(),
        'name' => $this->getName(),
        'startDateTime' => $this->getStartDateTime()->format(DateTimeInterface::RFC3339_EXTENDED),
        'isActive' => $this->getIsActive(),
        'isCompleted' => $this->getIsCompleted(),
        'bannerImageUrl' => $this->getBannerImageUrl(),
        'championship' => $this->getChampionship(),
        'track' => $this->getTrack(),
        'trackLayout' => $this->getTrackLayout(),
        'trophiesAwarded' => $this->getTrophiesAwarded(),
      ];
    }
  }

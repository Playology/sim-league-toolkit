<?php

  namespace SLTK\Domain;

  use Exception;
  use SLTK\Database\Repositories\EventClassesRepository;
  use SLTK\Database\Repositories\EventRefsRepository;
  use SLTK\Database\Repositories\StandaloneEventsRepository;
  use SLTK\Domain\Abstractions\AggregateRoot;
  use SLTK\Domain\Abstractions\Deletable;
  use SLTK\Domain\Abstractions\EventBase;
  use SLTK\Domain\Abstractions\Listable;
  use SLTK\Domain\Abstractions\ProvidesPersistableArray;
  use SLTK\Domain\Abstractions\Saveable;
  use SLTK\Domain\ValueObjects\ListingFilter;
  use stdClass;

  class StandaloneEvent extends EventBase implements AggregateRoot, Deletable, Listable, ProvidesPersistableArray, Saveable {
    private bool $isPublic = true;
    private bool $isTeamEvent = false;
    private int $maxEntrants = 0;
    private int $maxTeamSize = 0;
    private string $ruleSet = '';
    private ?int $ruleSetId = null;
    private string $scoringSet = '';
    private ?int $scoringSetId = null;
    private string $startTime = '';
    private bool $trophiesAwarded = false;


    /**
     * @throws Exception
     */
    public static function addStandaloneEventClass(int $standaloneEventId, int $eventClassId): void {
      $data = [
        'standaloneEventId' => $standaloneEventId,
        'eventClassId' => $eventClassId,
      ];

      StandaloneEventsRepository::addEventClass($data);
    }

    /**
     * @throws Exception
     */
    public static function deleteStandaloneEventClass(int $standaloneEventId, int $eventClassId): void {
      StandaloneEventsRepository::deleteEventClass($standaloneEventId, $eventClassId);
    }

    /**
     * @throws Exception
     */
    public static function updateStandaloneEventClass(int $standaloneEventId, int $eventClassId, array $updates): void {
      StandaloneEventsRepository::updateEventClass($standaloneEventId, $eventClassId, $updates);
    }

    /**
     * @return StandaloneEventEventClass[]
     * @throws Exception
     */
    public static function listClasses(int $standaloneEventId): array {
      $queryResults = EventClassesRepository::listForStandaloneEvent($standaloneEventId);

      return array_map(function(&$item) {
        return StandaloneEventEventClass::fromStdClass($item);
      }, $queryResults);
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
    }

    public static function fromStdClass(?stdClass $data): ?self {
      if(!$data) {
        return null;
      }

      $result = new self();

      $result->hydrateFromStdClass($data);

      return $result;
    }

    protected function hydrateFromStdClass(stdClass $data): void {
      parent::hydrateFromStdClass($data);
      $this->isPublic = (bool)($data->isPublic ?? true);
      $this->isTeamEvent = (bool)($data->isTeamEvent ?? false);
      $this->maxEntrants = (int)($data->maxEntrants ?? 0);
      $this->maxTeamSize = (int)($data->maxTeamSize ?? 0);
      $this->scoringSetId = isset($data->scoringSetId) && $data->scoringSetId > 0 ? (int)$data->scoringSetId : null;
      $this->scoringSet = $data->scoringSet ?? '';
      $this->ruleSetId = isset($data->ruleSetId) && $data->ruleSetId > 0 ? (int)$data->ruleSetId : null;
      $this->ruleSet = $data->ruleSet ?? '';
      $this->startTime = $data->startTime ?? '';
      $this->trophiesAwarded = (bool)($data->trophiesAwarded ?? false);
    }

    /**
     * @throws Exception
     */
    public static function get(int $id): ?self {
      $row = StandaloneEventsRepository::getById($id);

      return $row ? self::fromStdClass($row) : null;
    }

    /**
     * @throws Exception
     */
    public static function getByEventRefId(int $eventRefId): ?self {
      $row = StandaloneEventsRepository::getByEventRefId($eventRefId);

      return $row ? self::fromStdClass($row) : null;
    }

    /**
     * @throws Exception
     */
    public static function list(): array {
      $rows = StandaloneEventsRepository::listAll();

      return array_map(fn($row) => self::fromStdClass($row), $rows);
    }

    /**
     * @return StandaloneEvent[]
     * @throws Exception
     */
    public static function search(ListingFilter $filter): array {
      $rows = StandaloneEventsRepository::search($filter);

      return array_map(fn($row) => self::fromStdClass($row), $rows);
    }

    public function getEventType(): string {
      return 'standalone';
    }

    public function getIsPublic(): bool {
      return $this->isPublic;
    }

    public function setIsPublic(bool $value): void {
      $this->isPublic = $value;
    }

    public function getIsTeamEvent(): bool {
      return $this->isTeamEvent;
    }

    public function setIsTeamEvent(bool $value): void {
      $this->isTeamEvent = $value;
    }

    public function getMaxEntrants(): int {
      return $this->maxEntrants;
    }

    public function setMaxEntrants(int $value): void {
      $this->maxEntrants = $value;
    }

    public function getMaxTeamSize(): int {
      return $this->maxTeamSize;
    }

    public function setMaxTeamSize(int $value): void {
      $this->maxTeamSize = $value;
    }

    public function getRuleSet(): string {
      return $this->ruleSet;
    }

    public function getRuleSetId(): ?int {
      return $this->ruleSetId;
    }

    public function setRuleSetId(?int $value): void {
      $this->ruleSetId = $value;
    }

    public function getScoringSet(): string {
      return $this->scoringSet;
    }

    public function getStartTime(): string {
      return $this->startTime;
    }

    public function setStartTime(string $value): void {
      $this->startTime = $value;
    }

    public function getScoringSetId(): ?int {
      return $this->scoringSetId;
    }

    public function setScoringSetId(?int $value): void {
      $this->scoringSetId = $value;
    }

    public function getTrophiesAwarded(): bool {
      return $this->trophiesAwarded;
    }

    public function setTrophiesAwarded(bool $value): void {
      $this->trophiesAwarded = $value;
    }

    /**
     * @throws Exception
     */
    public function save(): self {
        if (!$this->hasId()) {
          $eventRefId = EventRefsRepository::add($this->getEventType());
          $this->setEventRefId($eventRefId);

          $this->setId(StandaloneEventsRepository::add($this->toArray()));
        } else {
          StandaloneEventsRepository::update($this->getId(), $this->toArray());
        }

        return $this;
    }

    public function toArray(): array {
      $result = $this->commonToArray();
      $result['scoringSetId'] = $this->getScoringSetId();
      $result['ruleSetId'] = $this->getRuleSetId();
      $result['maxEntrants'] = $this->getMaxEntrants();
      $result['isPublic'] = $this->getIsPublic();
      $result['isTeamEvent'] = $this->getIsTeamEvent();
      $result['maxTeamSize'] = $this->getMaxTeamSize();
      $result['startTime'] = $this->getStartTime();
      $result['trophiesAwarded'] = $this->getTrophiesAwarded();

      return $result;
    }

    public function toDto(): array {
      $result = $this->commonToDto();
      $result['eventType'] = $this->getEventType();
      $result['scoringSetId'] = $this->getScoringSetId();
      $result['scoringSet'] = $this->getScoringSet();
      $result['ruleSetId'] = $this->getRuleSetId();
      $result['ruleSet'] = $this->getRuleSet();
      $result['maxEntrants'] = $this->getMaxEntrants();
      $result['isPublic'] = $this->getIsPublic();
      $result['isTeamEvent'] = $this->getIsTeamEvent();
      $result['maxTeamSize'] = $this->getMaxTeamSize();
      $result['startTime'] = $this->getStartTime();
      $result['trophiesAwarded'] = $this->getTrophiesAwarded();

      return $result;
    }
  }

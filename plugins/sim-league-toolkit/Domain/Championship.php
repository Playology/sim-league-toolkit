<?php

  namespace SLTK\Domain;

  use DateInterval;
  use DateTime;
  use Exception;
  use SLTK\Core\Constants;
  use SLTK\Core\Enums\ChampionshipType;
  use SLTK\Database\Repositories\ChampionshipEventsRepository;
  use SLTK\Database\Repositories\ChampionshipRepository;
  use SLTK\Database\Repositories\EventClassesRepository;
  use SLTK\Domain\Abstractions\AggregateRoot;
  use SLTK\Domain\Abstractions\Deletable;
  use SLTK\Domain\Abstractions\Listable;
  use SLTK\Domain\Abstractions\ProvidesPersistableArray;
  use SLTK\Domain\Abstractions\Saveable;
  use SLTK\Domain\Traits\HasIdentity;
  use SLTK\Domain\ValueObjects\ListingFilter;
  use stdClass;

  class Championship implements AggregateRoot, Deletable, Listable, ProvidesPersistableArray, Saveable {
    use HasIdentity;

    private bool $allowEntryChange = false;
    private string $bannerImageUrl = '';
    private ChampionshipType $championshipType = ChampionshipType::Standard;
    private string $description = '';
    private int $entryChangeLimit = 0;
    private string $game = '';
    private int $gameId = Constants::DEFAULT_ID;
    private bool $isActive = false;
    private int $maxEntrants = 0;
    private string $name = '';
    private string $platform = '';
    private int $platformId = Constants::DEFAULT_ID;
    private int $resultsToDiscard = 0;
    private string $ruleSet;
    private ?int $ruleSetId = null;
    private ?string $scoringSet;
    private int $scoringSetId = Constants::DEFAULT_ID;
    private DateTime $startDate;
    private ?string $trackMasterTrack = null;
    private ?int $trackMasterTrackId = null;
    private ?string $trackMasterTrackLayout = null;
    private ?int $trackMasterTrackLayoutId = null;
    private bool $trophiesAwarded = false;

    public function __construct() {
      $this->startDate = (new DateTime())->add(new DateInterval('P1D'));
    }

    /**
     * @throws Exception
     */
    public static function addChampionshipClass(int $championshipId, int $eventClassId): void {
      $data = [
        'championshipId' => $championshipId,
        'eventClassId' => $eventClassId,
      ];

      ChampionshipRepository::addChampionshipClass($data);
    }

    /**
     * @throws Exception
     */
    public static function delete(int $id): void {
      ChampionshipRepository::delete($id);
    }

    /**
     * @throws Exception
     */
    public static function deleteChampionshipClass(int $championshipId, int $eventClassId): void {
      ChampionshipRepository::deleteClass($championshipId, $eventClassId);
    }

    /**
     * @throws Exception
     */
    public static function updateChampionshipClass(int $championshipId, int $eventClassId, array $updates): void {
      ChampionshipRepository::updateClass($championshipId, $eventClassId, $updates);
    }

    /**
     * @throws Exception
     */
    public static function deleteEvent(int $eventId): void {
      ChampionshipEvent::delete($eventId);
    }

    public static function fromStdClass(?stdClass $data): ?self {
      if (!$data) {
        return null;
      }

      $result = new self();
      $result->setId((int)$data->id);
      $result->setAllowEntryChange($data->allowEntryChange ?? false);
      $result->setBannerImageUrl($data->bannerImageUrl);
      $result->setChampionshipType(ChampionshipType::tryFrom($data->championshipType) ?? ChampionshipType::Standard);
      $result->setDescription($data->description ?? '');
      $result->setEntryChangeLimit($data->entryChangeLimit ?? 0);
      $result->setGame($data->game ?? '');
      $result->setGameId($data->gameId);
      $result->setIsActive($data->isActive);
      $result->setMaxEntrants((int)($data->maxEntrants ?? 0));
      $result->setName($data->name ?? '');
      $result->setPlatform($data->platform ?? '');
      $result->setPlatformId($data->platformId ?? Constants::DEFAULT_ID);
      $result->setResultsToDiscard($data->resultsToDiscard ?? 0);
      $result->setRuleSet($data->ruleSet ?? '');
      $result->setRuleSetId($data->ruleSetId ?? null);
      $result->setScoringSet($data->scoringSet ?? null);
      $result->setScoringSetId($data->scoringSetId ?? Constants::DEFAULT_ID);
      $result->setStartDate(DateTime::createFromFormat(Constants::STANDARD_DATE_FORMAT, $data->startDate));
      $result->setTrackMasterTrack($data->trackMasterTrack);
      $result->setTrackMasterTrackId($data->trackMasterTrackId ?? Constants::DEFAULT_ID);
      $result->setTrackMasterTrackLayout($data->trackMasterTrackLayout ?? null);
      $result->setTrackMasterTrackLayoutId($data->trackMasterTrackLayoutId ?? null);
      $result->setTrophiesAwarded($data->trophiesAwarded ?? false);

      return $result;
    }

    /**
     * @throws Exception
     */
    public static function get(int $id): Championship|null {
      $queryResult = ChampionshipRepository::getById($id);

      return Championship::fromStdClass($queryResult);
    }

    /**
     * @throws Exception
     */
    public static function getMostRecentForGame(int $gameId): Championship|null {
      $queryResult = ChampionshipRepository::getMostRecentForGame($gameId);

      return Championship::fromStdClass($queryResult);
    }

    /**
     * @throws Exception
     */
    public static function getEventById(int $id): ChampionshipEvent|null {
      $queryResult = ChampionshipEventsRepository::getById($id);

      return ChampionshipEvent::fromStdClass($queryResult);
    }

    /**
     * @return Championship[]
     * @throws Exception
     */
    public static function list(): array {
      $queryResults = ChampionshipRepository::listAll();

      return array_map(function ($item) {
        return Championship::fromStdClass($item);
      }, $queryResults);
    }

    /**
     * @return Championship[]
     * @throws Exception
     */
    public static function search(ListingFilter $filter): array {
      $queryResults = ChampionshipRepository::search($filter);

      return array_map(function ($item) {
        return Championship::fromStdClass($item);
      }, $queryResults);
    }

    /**
     * @return ChampionshipEventClass[]
     * @throws Exception
     */
    public static function listClasses($id): array {
      $queryResults = EventClassesRepository::listForChampionship($id);

      return array_map(function(&$item) {
        return ChampionshipEventClass::fromStdClass($item);
      }, $queryResults);
    }

    /**
     * @return ChampionshipEvent[]
     * @throws Exception
     */
    public static function listEvents(int $id): array {
      $queryResults = ChampionshipEventsRepository::listByChampionshipId($id);

      return array_map(function ($item) {
        return ChampionshipEvent::fromStdClass($item);
      }, $queryResults);
    }

    public function getAllowEntryChange(): bool {
      return $this->allowEntryChange ?? false;
    }

    public function setAllowEntryChange(bool $value): void {
      $this->allowEntryChange = $value;
    }

    public function getBannerImageUrl(): string {
      return $this->bannerImageUrl ?? '';
    }

    public function setBannerImageUrl(string $value): void {
      $this->bannerImageUrl = $value;
    }

    public function getChampionshipType(): ChampionshipType {
      return $this->championshipType ?? ChampionshipType::Standard;
    }

    public function setChampionshipType(ChampionshipType $value): void {
      $this->championshipType = $value;
    }

    public function getDescription(): string {
      return trim($this->description ?? '');
    }

    public function setDescription(string $value): void {
      $this->description = trim($value);
    }

    public function getEntryChangeLimit(): int {
      return $this->entryChangeLimit ?? 0;
    }

    public function setEntryChangeLimit(int $value): void {
      $this->entryChangeLimit = $value;
    }

    public function getFormattedStartDate(): string {
      return date_format($this->getStartDate(), Constants::STANDARD_DATE_FORMAT);
    }

    public function getGame(): string {
      return $this->game ?? '';
    }

    public function setGame(string $value): void {
      $this->game = $value;
    }

    public function getGameId(): int {
      return $this->gameId ?? Constants::DEFAULT_ID;
    }

    public function setGameId(int $value): void {
      $this->gameId = $value;
    }

    public function getIsActive(): bool {
      return $this->isActive ?? false;
    }

    public function setIsActive(bool $value): void {
      $this->isActive = $value;
    }

    public function getMaxEntrants(): int {
      return $this->maxEntrants ?? 0;
    }

    public function setMaxEntrants(int $value): void {
      $this->maxEntrants = $value;
    }

    public function getName(): string {
      return trim($this->name ?? '');
    }

    public function setName(string $value): void {
      $this->name = trim($value);
    }

    public function getPlatform(): string {
      return $this->platform ?? '';
    }

    public function setPlatform(string $value): void {
      $this->platform = $value;
    }

    public function getPlatformId(): int {
      return $this->platformId ?? Constants::DEFAULT_ID;
    }

    public function setPlatformId(int $value): void {
      $this->platformId = $value;
    }

    public function getResultsToDiscard(): int {
      return $this->resultsToDiscard ?? 0;
    }

    public function setResultsToDiscard(int $value): void {
      $this->resultsToDiscard = $value;
    }

    public function getRuleSet(): string {
      return $this->ruleSet ?? '';
    }

    public function setRuleSet(string $value): void {
      $this->ruleSet = $value;
    }

    public function getRuleSetId(): ?int {
      return $this->ruleSetId > 0 ? (int)$this->ruleSetId : null;
    }

    public function setRuleSetId(?int $value): void {
      $this->ruleSetId = $value;
    }

    public function getScoringSet(): string {
      return $this->scoringSet ?? '';
    }

    public function setScoringSet(string $value): void {
      $this->scoringSet = $value;
    }

    public function getScoringSetId(): int {
      return $this->scoringSetId ?? Constants::DEFAULT_ID;
    }

    public function setScoringSetId(int $value): void {
      $this->scoringSetId = $value;
    }

    public function getStartDate(): DateTime {
      return $this->startDate ?? (new DateTime())->add(new DateInterval('P1D'));
    }

    public function setStartDate(DateTime $value): void {
      $this->startDate = $value;
    }

    public function getTrackMasterTrack(): ?string {
      return $this->trackMasterTrack ?? null;
    }

    public function setTrackMasterTrack(?string $value): void {
      $this->trackMasterTrack = $value;
    }

    public function getTrackMasterTrackId(): ?int {
      return $this->trackMasterTrackId > 0 ? (int)$this->trackMasterTrackId : null;
    }

    public function setTrackMasterTrackId(?int $value): void {
      $this->trackMasterTrackId = $value;
    }

    public function getTrackMasterTrackLayout(): ?string {
      return $this->trackMasterTrackLayout ?? null;
    }

    public function setTrackMasterTrackLayout(?string $value): void {
      $this->trackMasterTrackLayout = $value;
    }

    public function getTrackMasterTrackLayoutId(): ?int {
      return $this->trackMasterTrackLayoutId > 0 ? (int)$this->trackMasterTrackLayoutId : null;
    }

    public function setTrackMasterTrackLayoutId(?int $value): void {
      $this->trackMasterTrackLayoutId = $value;
    }

    public function getTrophiesAwarded(): bool {
      return $this->trophiesAwarded ?? false;
    }

    public function setTrophiesAwarded(bool $value): void {
      $this->trophiesAwarded = $value;
    }

    /**
     * @throws Exception
     */
    public function save(): self {
      if (!$this->hasId()) {
        $this->setId(ChampionshipRepository::add($this->toArray()));
      } else {
        ChampionshipRepository::update($this->getId(), $this->toArray());
      }

      return $this;
    }

    /**
     * @return array{fieldName: string, value: mixed}
     */
    public function toArray(): array {
      return [
        'allowEntryChange' => $this->getAllowEntryChange(),
        'bannerImageUrl' => $this->getBannerImageUrl(),
        'championshipType' => $this->getChampionshipType()->value,
        'description' => $this->getDescription(),
        'entryChangeLimit' => $this->getEntryChangeLimit(),
        'gameId' => $this->getGameId(),
        'isActive' => $this->getIsActive(),
        'maxEntrants' => $this->getMaxEntrants(),
        'name' => $this->getName(),
        'platformId' => $this->getPlatformId(),
        'resultsToDiscard' => $this->getResultsToDiscard(),
        'ruleSetId' => $this->getRuleSetId(),
        'scoringSetId' => $this->getScoringSetId(),
        'startDate' => $this->getFormattedStartDate(),
        'trackMasterTrackId' => $this->getTrackMasterTrackId(),
        'trackMasterTrackLayoutId' => $this->getTrackMasterTrackLayoutId(),
        'trophiesAwarded' => $this->getTrophiesAwarded(),
      ];
    }

    public function toDto(): array {

      return [
        'id' => $this->getId(),
        'allowEntryChange' => $this->getAllowEntryChange(),
        'bannerImageUrl' => $this->getBannerImageUrl(),
        'championshipType' => $this->getChampionshipType(),
        'description' => $this->getDescription(),
        'entryChangeLimit' => $this->getEntryChangeLimit(),
        'game' => $this->getGame(),
        'gameId' => $this->getGameId(),
        'isActive' => $this->getIsActive(),
        'maxEntrants' => $this->getMaxEntrants(),
        'name' => $this->getName(),
        'platform' => $this->getPlatform(),
        'platformId' => $this->getPlatformId(),
        'resultsToDiscard' => $this->getResultsToDiscard(),
        'ruleSet' => $this->getRuleSet(),
        'ruleSetId' => $this->getRuleSetId(),
        'scoringSet' => $this->getScoringSet(),
        'scoringSetId' => $this->getScoringSetId(),
        'startDate' => $this->getFormattedStartDate(),
        'trackMasterTrack' => $this->getTrackMasterTrack(),
        'trackMasterTrackId' => $this->getTrackMasterTrackId(),
        'trackMasterTrackLayoutId' => $this->getTrackMasterTrackLayoutId(),
        'trophiesAwarded' => $this->getTrophiesAwarded(),
      ];
    }
  }
<?php

  namespace SLTK\Domain;

  use DateInterval;
  use DateTime;
  use Exception;
  use SLTK\Core\Constants;
  use SLTK\Core\Enums\ChampionshipType;
  use SLTK\Core\Enums\PlanStatus;
  use SLTK\Database\Repositories\ChampionshipPlanRepository;
  use SLTK\Domain\Abstractions\AggregateRoot;
  use SLTK\Domain\Abstractions\Deletable;
  use SLTK\Domain\Abstractions\Listable;
  use SLTK\Domain\Abstractions\ProvidesPersistableArray;
  use SLTK\Domain\Abstractions\Saveable;
  use SLTK\Domain\Traits\HasIdentity;
  use stdClass;

  class ChampionshipPlan implements AggregateRoot, Deletable, Listable, ProvidesPersistableArray, Saveable {
    use HasIdentity;

    private bool $classesFixed = true;
    private ?int $createdChampionshipId = null;
    private string $description = '';
    private string $game = '';
    private int $gameId = Constants::DEFAULT_ID;
    private int $maxCarVotesPerUser = 1;
    private int $maxCarsPerClass = 1;
    private int $maxTrackVotesPerUser = 1;
    private string $name = '';
    private string $platform = '';
    private int $platformId = Constants::DEFAULT_ID;
    private ChampionshipType $championshipType = ChampionshipType::Standard;
    private DateTime $startDate;
    private PlanStatus $status = PlanStatus::Draft;

    public function __construct() {
      $this->startDate = (new DateTime())->add(new DateInterval('P1D'));
    }

    /**
     * @throws Exception
     */
    public static function addCar(int $planId, int $carId): void {
      ChampionshipPlanRepository::addCar($planId, $carId);
    }

    /**
     * @throws Exception
     */
    public static function addClass(int $planId, array $classData): int {
      return ChampionshipPlanRepository::addClass($planId, $classData);
    }

    /**
     * @throws Exception
     */
    public static function addTrack(int $planId, int $trackId): void {
      ChampionshipPlanRepository::addTrack($planId, $trackId);
    }

    /**
     * @throws Exception
     */
    public static function delete(int $id): void {
      ChampionshipPlanRepository::delete($id);
    }

    public static function fromStdClass(?stdClass $data): ?self {
      if (!$data) {
        return null;
      }

      $result = new self();
      $result->setId((int)$data->id);
      $result->setChampionshipType(ChampionshipType::tryFrom($data->championshipType) ?? ChampionshipType::Standard);
      $result->setClassesFixed((bool)($data->classesFixed ?? true));
      $result->setCreatedChampionshipId(isset($data->createdChampionshipId) ? (int)$data->createdChampionshipId : null);
      $result->setDescription($data->description ?? '');
      $result->setGame($data->game ?? '');
      $result->setGameId($data->gameId);
      $result->setMaxCarVotesPerUser((int)($data->maxCarVotesPerUser ?? 1));
      $result->setMaxCarsPerClass((int)($data->maxCarsPerClass ?? 1));
      $result->setMaxTrackVotesPerUser((int)($data->maxTrackVotesPerUser ?? 1));
      $result->setName($data->name ?? '');
      $result->setPlatform($data->platform ?? '');
      $result->setPlatformId($data->platformId ?? Constants::DEFAULT_ID);
      $result->setStartDate(DateTime::createFromFormat(Constants::STANDARD_DATE_FORMAT, $data->startDate));
      $result->setStatus(PlanStatus::tryFrom($data->status) ?? PlanStatus::Draft);

      return $result;
    }

    /**
     * @throws Exception
     */
    public static function get(int $id): ?self {
      $queryResult = ChampionshipPlanRepository::getById($id);

      return self::fromStdClass($queryResult);
    }

    /**
     * @return self[]
     * @throws Exception
     */
    public static function list(): array {
      $queryResults = ChampionshipPlanRepository::listAll();

      return array_map(fn($item) => self::fromStdClass($item), $queryResults);
    }

    /**
     * @return self[]
     * @throws Exception
     */
    public static function listOpen(): array {
      $queryResults = ChampionshipPlanRepository::listByStatus(PlanStatus::Open);

      return array_map(fn($item) => self::fromStdClass($item), $queryResults);
    }

    /**
     * @throws Exception
     */
    public static function removeCar(int $planId, int $carId): void {
      ChampionshipPlanRepository::removeCar($planId, $carId);
    }

    /**
     * @throws Exception
     */
    public static function removeClass(int $planId, int $planClassId): void {
      ChampionshipPlanRepository::removeClass($planId, $planClassId);
    }

    /**
     * @throws Exception
     */
    public static function removeTrack(int $planId, int $trackId): void {
      ChampionshipPlanRepository::removeTrack($planId, $trackId);
    }

    public function getChampionshipType(): ChampionshipType {
      return $this->championshipType ?? ChampionshipType::Standard;
    }

    public function setChampionshipType(ChampionshipType $value): void {
      $this->championshipType = $value;
    }

    public function getClassesFixed(): bool {
      return $this->classesFixed ?? true;
    }

    public function setClassesFixed(bool $value): void {
      $this->classesFixed = $value;
    }

    public function getCreatedChampionshipId(): ?int {
      return $this->createdChampionshipId;
    }

    public function setCreatedChampionshipId(?int $value): void {
      $this->createdChampionshipId = $value;
    }

    public function getDescription(): string {
      return trim($this->description ?? '');
    }

    public function setDescription(string $value): void {
      $this->description = trim($value);
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

    public function getMaxCarVotesPerUser(): int {
      return $this->maxCarVotesPerUser ?? 1;
    }

    public function setMaxCarVotesPerUser(int $value): void {
      $this->maxCarVotesPerUser = $value;
    }

    public function getMaxCarsPerClass(): int {
      return $this->maxCarsPerClass ?? 1;
    }

    public function setMaxCarsPerClass(int $value): void {
      $this->maxCarsPerClass = $value;
    }

    public function getMaxTrackVotesPerUser(): int {
      return $this->maxTrackVotesPerUser ?? 1;
    }

    public function setMaxTrackVotesPerUser(int $value): void {
      $this->maxTrackVotesPerUser = $value;
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

    public function getStartDate(): DateTime {
      return $this->startDate ?? (new DateTime())->add(new DateInterval('P1D'));
    }

    public function setStartDate(DateTime $value): void {
      $this->startDate = $value;
    }

    public function getStatus(): PlanStatus {
      return $this->status ?? PlanStatus::Draft;
    }

    public function setStatus(PlanStatus $value): void {
      $this->status = $value;
    }

    public function isTrackMaster(): bool {
      return $this->getChampionshipType() === ChampionshipType::TrackMaster;
    }

    public function isOpen(): bool {
      return $this->getStatus() === PlanStatus::Open;
    }

    /**
     * @throws Exception
     */
    public function save(): self {
      if (!$this->hasId()) {
        $this->setId(ChampionshipPlanRepository::add($this->toArray()));
      } else {
        ChampionshipPlanRepository::update($this->getId(), $this->toArray());
      }

      return $this;
    }

    public function toArray(): array {
      return [
        'name' => $this->getName(),
        'description' => $this->getDescription(),
        'gameId' => $this->getGameId(),
        'platformId' => $this->getPlatformId(),
        'championshipType' => $this->getChampionshipType()->value,
        'status' => $this->getStatus()->value,
        'startDate' => $this->getFormattedStartDate(),
        'classesFixed' => $this->getClassesFixed(),
        'maxTrackVotesPerUser' => $this->getMaxTrackVotesPerUser(),
        'maxCarVotesPerUser' => $this->getMaxCarVotesPerUser(),
        'maxCarsPerClass' => $this->getMaxCarsPerClass(),
        'createdChampionshipId' => $this->getCreatedChampionshipId(),
      ];
    }

    public function toDto(): array {
      return [
        'id' => $this->getId(),
        'name' => $this->getName(),
        'description' => $this->getDescription(),
        'game' => $this->getGame(),
        'gameId' => $this->getGameId(),
        'platform' => $this->getPlatform(),
        'platformId' => $this->getPlatformId(),
        'championshipType' => $this->getChampionshipType(),
        'status' => $this->getStatus(),
        'startDate' => $this->getFormattedStartDate(),
        'classesFixed' => $this->getClassesFixed(),
        'maxTrackVotesPerUser' => $this->getMaxTrackVotesPerUser(),
        'maxCarVotesPerUser' => $this->getMaxCarVotesPerUser(),
        'maxCarsPerClass' => $this->getMaxCarsPerClass(),
        'createdChampionshipId' => $this->getCreatedChampionshipId(),
      ];
    }
  }

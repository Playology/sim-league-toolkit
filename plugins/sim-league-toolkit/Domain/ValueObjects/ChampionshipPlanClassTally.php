<?php

  namespace SLTK\Domain\ValueObjects;

  use stdClass;

  final class ChampionshipPlanClassTally {
    public function __construct(
      public readonly int $planClassId,
      public readonly ?int $eventClassId,
      public readonly string $name,
      public readonly string $carClass,
      public readonly ?int $driverCategoryId,
      public readonly ?string $driverCategoryName,
      public readonly bool $isSingleCarClass,
      public readonly ?int $singleCarId,
      public readonly ?string $singleCarName,
      public readonly ?int $suggestedByUserId,
      public readonly ?string $suggestedByName,
      public readonly int $voteCount,
      public readonly bool $currentUserVoted,
    ) {}

    public static function fromStdClass(stdClass $data): self {
      return new self(
        planClassId: (int)$data->planClassId,
        eventClassId: isset($data->eventClassId) ? (int)$data->eventClassId : null,
        name: $data->name ?? '',
        carClass: $data->carClass ?? '',
        driverCategoryId: isset($data->driverCategoryId) ? (int)$data->driverCategoryId : null,
        driverCategoryName: $data->driverCategoryName ?? null,
        isSingleCarClass: (bool)($data->isSingleCarClass ?? false),
        singleCarId: isset($data->singleCarId) ? (int)$data->singleCarId : null,
        singleCarName: $data->singleCarName ?? null,
        suggestedByUserId: isset($data->suggestedByUserId) ? (int)$data->suggestedByUserId : null,
        suggestedByName: $data->suggestedByName ?? null,
        voteCount: (int)($data->voteCount ?? 0),
        currentUserVoted: (bool)($data->currentUserVoted ?? false),
      );
    }

    public function toDto(): array {
      return [
        'planClassId' => $this->planClassId,
        'eventClassId' => $this->eventClassId,
        'name' => $this->name,
        'carClass' => $this->carClass,
        'driverCategoryId' => $this->driverCategoryId,
        'driverCategoryName' => $this->driverCategoryName,
        'isSingleCarClass' => $this->isSingleCarClass,
        'singleCarId' => $this->singleCarId,
        'singleCarName' => $this->singleCarName,
        'suggestedByUserId' => $this->suggestedByUserId,
        'suggestedByName' => $this->suggestedByName ?? 'Admin',
        'voteCount' => $this->voteCount,
        'currentUserVoted' => $this->currentUserVoted,
      ];
    }
  }

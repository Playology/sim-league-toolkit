<?php

  namespace SLTK\Domain\ValueObjects;

  use stdClass;

  final class ChampionshipPlanCarTally {
    public function __construct(
      public readonly int $carId,
      public readonly string $carName,
      public readonly string $carClass,
      public readonly int $voteCount,
      public readonly bool $currentUserVoted,
    ) {}

    public static function fromStdClass(stdClass $data): self {
      return new self(
        carId: (int)$data->carId,
        carName: $data->carName ?? '',
        carClass: $data->carClass ?? '',
        voteCount: (int)($data->voteCount ?? 0),
        currentUserVoted: (bool)($data->currentUserVoted ?? false),
      );
    }

    public function toDto(): array {
      return [
        'carId' => $this->carId,
        'carName' => $this->carName,
        'carClass' => $this->carClass,
        'voteCount' => $this->voteCount,
        'currentUserVoted' => $this->currentUserVoted,
      ];
    }
  }

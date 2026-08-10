<?php

  namespace SLTK\Domain\ValueObjects;

  final class ChampionshipBuilderRoundPlan {
    public function __construct(
      public readonly ?int $trackId = null,
      public readonly ?int $trackLayoutId = null,
      public readonly ?int $carId = null,
    ) {}

    public static function fromArray(array $data): self {
      $trackId = isset($data['trackId']) ? (int)$data['trackId'] : null;
      $trackLayoutId = isset($data['trackLayoutId']) ? (int)$data['trackLayoutId'] : null;
      $carId = isset($data['carId']) ? (int)$data['carId'] : null;

      return new self(
        trackId: $trackId !== null && $trackId > 0 ? $trackId : null,
        trackLayoutId: $trackLayoutId !== null && $trackLayoutId > 0 ? $trackLayoutId : null,
        carId: $carId !== null && $carId > 0 ? $carId : null,
      );
    }
  }

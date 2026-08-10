<?php

  namespace SLTK\Domain\ValueObjects;

  use SLTK\Core\Constants;

  final class ChampionshipBuilderClassPlan {
    public function __construct(
      public readonly ?int $eventClassId,
      public readonly string $name = '',
      public readonly string $carClass = '',
      public readonly bool $isSingleCarClass = false,
      public readonly ?int $singleCarId = null,
      public readonly int $driverCategoryId = Constants::DEFAULT_ID,
    ) {}

    public function isNew(): bool {
      return $this->eventClassId === null;
    }

    public static function fromArray(array $data): self {
      $eventClassId = isset($data['eventClassId']) ? (int)$data['eventClassId'] : null;
      $singleCarId = isset($data['singleCarId']) ? (int)$data['singleCarId'] : null;

      return new self(
        eventClassId: $eventClassId !== null && $eventClassId > 0 ? $eventClassId : null,
        name: (string)($data['name'] ?? ''),
        carClass: (string)($data['carClass'] ?? ''),
        isSingleCarClass: (bool)($data['isSingleCarClass'] ?? false),
        singleCarId: $singleCarId !== null && $singleCarId > 0 ? $singleCarId : null,
        driverCategoryId: (int)($data['driverCategoryId'] ?? Constants::DEFAULT_ID),
      );
    }
  }

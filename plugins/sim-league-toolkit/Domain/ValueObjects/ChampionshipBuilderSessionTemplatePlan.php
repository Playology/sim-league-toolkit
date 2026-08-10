<?php

  namespace SLTK\Domain\ValueObjects;

  final class ChampionshipBuilderSessionTemplatePlan {
    public function __construct(
      public readonly string $sessionType,
      public readonly int $count,
      public readonly array $attributes,
    ) {}

    public static function fromArray(array $data): self {
      return new self(
        sessionType: (string)($data['sessionType'] ?? ''),
        count: max(0, (int)($data['count'] ?? 0)),
        attributes: (array)($data['attributes'] ?? []),
      );
    }
  }

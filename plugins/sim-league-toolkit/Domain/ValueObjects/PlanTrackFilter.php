<?php

  namespace SLTK\Domain\ValueObjects;

  final class PlanTrackFilter {
    public function __construct(
      public readonly bool $excludeLastChampionshipTracks = false,
      public readonly bool $excludeDlc = false,
      public readonly ?int $minLength = null,
      public readonly ?int $maxLength = null,
    ) {}

    public static function fromArray(array $data): self {
      $minLength = isset($data['minLength']) && $data['minLength'] !== '' ? (int)$data['minLength'] : null;
      $maxLength = isset($data['maxLength']) && $data['maxLength'] !== '' ? (int)$data['maxLength'] : null;

      return new self(
        excludeLastChampionshipTracks: filter_var($data['excludeLastChampionshipTracks'] ?? false, FILTER_VALIDATE_BOOLEAN),
        excludeDlc: filter_var($data['excludeDlc'] ?? false, FILTER_VALIDATE_BOOLEAN),
        minLength: $minLength,
        maxLength: $maxLength,
      );
    }
  }

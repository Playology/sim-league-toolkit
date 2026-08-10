<?php

  namespace SLTK\Domain;

  class NationalStandingLine {
    public function __construct(
      private readonly int $countryId,
      private readonly string $countryName,
      private readonly string $alpha3,
      private readonly float $totalPoints,
    ) {
    }

    public function getAlpha3(): string {
      return $this->alpha3;
    }

    public function getCountryId(): int {
      return $this->countryId;
    }

    public function getCountryName(): string {
      return $this->countryName;
    }

    public function getTotalPoints(): float {
      return $this->totalPoints;
    }
  }

<?php

  namespace SLTK\Domain;

  class TeamStandingLine {
    public function __construct(
      private readonly int $teamId,
      private readonly string $teamName,
      private readonly string $logoUrl,
      private readonly float $totalPoints,
    ) {
    }

    public function getLogoUrl(): string {
      return $this->logoUrl;
    }

    public function getTeamId(): int {
      return $this->teamId;
    }

    public function getTeamName(): string {
      return $this->teamName;
    }

    public function getTotalPoints(): float {
      return $this->totalPoints;
    }
  }

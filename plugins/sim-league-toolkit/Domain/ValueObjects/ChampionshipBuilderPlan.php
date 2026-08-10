<?php

  namespace SLTK\Domain\ValueObjects;

  use DateTime;
  use DateTimeInterface;
  use DateTimeZone;
  use SLTK\Core\Constants;
  use SLTK\Core\Enums\ChampionshipType;

  final class ChampionshipBuilderPlan {
    /**
     * @param ChampionshipBuilderClassPlan[] $classes
     * @param ChampionshipBuilderRoundPlan[] $rounds
     * @param ChampionshipBuilderSessionTemplatePlan[] $sessionTemplates
     */
    public function __construct(
      public readonly string $name,
      public readonly string $description,
      public readonly int $gameId,
      public readonly int $platformId,
      public readonly ChampionshipType $championshipType,
      public readonly DateTime $startDate,
      public readonly ?int $ruleSetId,
      public readonly int $scoringSetId,
      public readonly int $resultsToDiscard,
      public readonly int $entryChangeLimit,
      public readonly int $maxEntrants,
      public readonly string $eventStartTime,
      public readonly int $eventStartIntervalDays,
      public readonly ?int $trackMasterTrackId,
      public readonly ?int $trackMasterTrackLayoutId,
      public readonly array $classes,
      public readonly array $rounds,
      public readonly array $sessionTemplates,
    ) {}

    public function isTrackMaster(): bool {
      return $this->championshipType === ChampionshipType::TrackMaster;
    }

    public static function fromArray(array $data): self {
      $startDate = DateTime::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, (string)($data['startDate'] ?? ''), new DateTimeZone('UTC')) ?: new DateTime();

      $ruleSetId = isset($data['ruleSetId']) ? (int)$data['ruleSetId'] : null;
      $trackMasterTrackId = isset($data['trackMasterTrackId']) ? (int)$data['trackMasterTrackId'] : null;
      $trackMasterTrackLayoutId = isset($data['trackMasterTrackLayoutId']) ? (int)$data['trackMasterTrackLayoutId'] : null;

      return new self(
        name: (string)($data['name'] ?? ''),
        description: (string)($data['description'] ?? ''),
        gameId: (int)($data['gameId'] ?? Constants::DEFAULT_ID),
        platformId: (int)($data['platformId'] ?? Constants::DEFAULT_ID),
        championshipType: ChampionshipType::tryFrom((string)($data['championshipType'] ?? '')) ?? ChampionshipType::Standard,
        startDate: $startDate,
        ruleSetId: $ruleSetId !== null && $ruleSetId > 0 ? $ruleSetId : null,
        scoringSetId: (int)($data['scoringSetId'] ?? Constants::DEFAULT_ID),
        resultsToDiscard: (int)($data['resultsToDiscard'] ?? 0),
        entryChangeLimit: (int)($data['entryChangeLimit'] ?? 1),
        maxEntrants: (int)($data['maxEntrants'] ?? 0),
        eventStartTime: (string)($data['eventStartTime'] ?? '14:00'),
        eventStartIntervalDays: max(1, (int)($data['eventStartIntervalDays'] ?? 7)),
        trackMasterTrackId: $trackMasterTrackId !== null && $trackMasterTrackId > 0 ? $trackMasterTrackId : null,
        trackMasterTrackLayoutId: $trackMasterTrackLayoutId !== null && $trackMasterTrackLayoutId > 0 ? $trackMasterTrackLayoutId : null,
        classes: array_map(fn($c) => ChampionshipBuilderClassPlan::fromArray((array)$c), $data['classes'] ?? []),
        rounds: array_map(fn($r) => ChampionshipBuilderRoundPlan::fromArray((array)$r), $data['rounds'] ?? []),
        sessionTemplates: array_map(fn($s) => ChampionshipBuilderSessionTemplatePlan::fromArray((array)$s), $data['sessionTemplates'] ?? []),
      );
    }
  }

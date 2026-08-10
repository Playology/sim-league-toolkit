<?php

  namespace SLTK\Domain\ValueObjects;

  use stdClass;

  final class ChampionshipPlanTrackTally {
    public function __construct(
      public readonly int $trackId,
      public readonly string $trackName,
      public readonly int $voteCount,
      public readonly int $favouriteCount,
      public readonly bool $currentUserVoted,
      public readonly bool $currentUserFavourited,
    ) {}

    public static function fromStdClass(stdClass $data): self {
      return new self(
        trackId: (int)$data->trackId,
        trackName: $data->trackName ?? '',
        voteCount: (int)($data->voteCount ?? 0),
        favouriteCount: (int)($data->favouriteCount ?? 0),
        currentUserVoted: (bool)($data->currentUserVoted ?? false),
        currentUserFavourited: (bool)($data->currentUserFavourited ?? false),
      );
    }

    public function toDto(): array {
      return [
        'trackId' => $this->trackId,
        'trackName' => $this->trackName,
        'voteCount' => $this->voteCount,
        'favouriteCount' => $this->favouriteCount,
        'currentUserVoted' => $this->currentUserVoted,
        'currentUserFavourited' => $this->currentUserFavourited,
      ];
    }
  }

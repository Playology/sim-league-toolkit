<?php

  namespace SLTK\Domain;

  use Exception;

  class TeamStandingsCalculator {
    /**
     * @return TeamStandingLine[] sorted by total points, descending
     * @throws Exception
     */
    public static function calculate(int $championshipId): array {
      $totalsByTeamId = [];
      $teamsById = [];

      foreach (ChampionshipStandingsCalculator::aggregateEntryPoints($championshipId) as $entry) {
        foreach (Team::listByMemberId($entry['userId']) as $team) {
          $teamsById[$team->getId()] = $team;
          $totalsByTeamId[$team->getId()] = ($totalsByTeamId[$team->getId()] ?? 0) + $entry['points'];
        }
      }

      $standings = array_map(
        fn(int $teamId, float $points) => new TeamStandingLine($teamId, $teamsById[$teamId]->getName(), $teamsById[$teamId]->getLogoUrl(), $points),
        array_keys($totalsByTeamId),
        array_values($totalsByTeamId)
      );

      usort($standings, fn(TeamStandingLine $a, TeamStandingLine $b) => $b->getTotalPoints() <=> $a->getTotalPoints());

      return $standings;
    }
  }

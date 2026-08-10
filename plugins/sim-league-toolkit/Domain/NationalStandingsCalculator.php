<?php

  namespace SLTK\Domain;

  use Exception;
  use SLTK\Core\UserMetaKeys;

  class NationalStandingsCalculator {
    /**
     * @return NationalStandingLine[] sorted by total points, descending
     * @throws Exception
     */
    public static function calculate(int $championshipId): array {
      $totalsByCountryId = [];

      foreach (ChampionshipStandingsCalculator::aggregateEntryPoints($championshipId) as $entry) {
        $countryId = (int)get_user_meta($entry['userId'], UserMetaKeys::COUNTRY_ID, true);

        if ($countryId <= 0) {
          continue;
        }

        $totalsByCountryId[$countryId] = ($totalsByCountryId[$countryId] ?? 0) + $entry['points'];
      }

      $standings = array_map(function (int $countryId, float $points) {
        $country = Country::get($countryId);

        return new NationalStandingLine($countryId, $country->getName(), $country->getAlpha3(), $points);
      }, array_keys($totalsByCountryId), array_values($totalsByCountryId));

      usort($standings, fn(NationalStandingLine $a, NationalStandingLine $b) => $b->getTotalPoints() <=> $a->getTotalPoints());

      return $standings;
    }
  }

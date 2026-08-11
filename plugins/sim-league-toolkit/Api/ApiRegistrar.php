<?php

  namespace SLTK\Api;

  class ApiRegistrar {

    public const string API_NAMESPACE = 'sltk/v1';
    private static array $routeMap = [
      // More specific patterns first
      '#/' . ResourceNames::EVENT_REFS . '/\d+/event-sessions#' => EventSessionApiController::class,
      '#/' . ResourceNames::EVENT_SESSION . '/\d+/championship-results#' => ChampionshipSessionResultApiController::class,
      '#/' . ResourceNames::EVENT_SESSION . '/\d+/standalone-results#' => StandaloneSessionResultApiController::class,
      '#/' . ResourceNames::EVENT_SESSION . '#' => EventSessionApiController::class,
      '#/' . ResourceNames::CHAMPIONSHIP_BUILDER . '#' => ChampionshipBuilderApiController::class,
      '#/' . ResourceNames::CHAMPIONSHIP_PLAN . '/\d+/vote#' => ChampionshipPlanVoteApiController::class,
      '#/' . ResourceNames::CHAMPIONSHIP_PLAN . '#' => ChampionshipPlanApiController::class,
      '#/' . ResourceNames::CHAMPIONSHIP . '/\d+/classes#' => ChampionshipClassApiController::class,
      '#/' . ResourceNames::CHAMPIONSHIP_EVENT . '/\d+/trophies/preview#' => TrophyApiController::class,
      '#/' . ResourceNames::CHAMPIONSHIP_EVENT . '/\d+/trophies/award#' => TrophyApiController::class,
      '#/' . ResourceNames::CHAMPIONSHIP_EVENT . '#' => ChampionshipEventApiController::class,
      '#/' . ResourceNames::CHAMPIONSHIP . '/\d+/events#' => ChampionshipEventApiController::class,
      '#/' . ResourceNames::CHAMPIONSHIP_ENTRY . '#' => ChampionshipEntryApiController::class,
      '#/' . ResourceNames::CHAMPIONSHIP . '/\d+/entries#' => ChampionshipEntryApiController::class,
      '#/' . ResourceNames::CHAMPIONSHIP_RESULT_PENALTY . '#' => ChampionshipResultPenaltyApiController::class,
      '#/' . ResourceNames::CHAMPIONSHIP_SESSION_RESULT . '/\d+/penalties#' => ChampionshipResultPenaltyApiController::class,
      '#/' . ResourceNames::CHAMPIONSHIP_SESSION_RESULT . '#' => ChampionshipSessionResultApiController::class,
      '#/' . ResourceNames::STANDALONE_RESULT_PENALTY . '#' => StandaloneResultPenaltyApiController::class,
      '#/' . ResourceNames::STANDALONE_SESSION_RESULT . '/\d+/penalties#' => StandaloneResultPenaltyApiController::class,
      '#/' . ResourceNames::STANDALONE_SESSION_RESULT . '#' => StandaloneSessionResultApiController::class,
      '#/' . ResourceNames::STANDALONE_EVENT . '/\d+/classes/available#' => StandaloneEventClassApiController::class,
      '#/' . ResourceNames::STANDALONE_EVENT . '/\d+/classes#' => StandaloneEventClassApiController::class,
      '#/' . ResourceNames::STANDALONE_EVENT_ENTRY . '#' => StandaloneEventEntryApiController::class,
      '#/' . ResourceNames::STANDALONE_EVENT . '/\d+/entries#' => StandaloneEventEntryApiController::class,
      '#/' . ResourceNames::STANDALONE_EVENT . '/\d+/trophies/preview#' => TrophyApiController::class,
      '#/' . ResourceNames::STANDALONE_EVENT . '/\d+/trophies/award#' => TrophyApiController::class,
      '#/' . ResourceNames::STANDALONE_EVENT . '#' => StandaloneEventApiController::class,
      '#/' . ResourceNames::CHAMPIONSHIP . '/\d+/trophies/preview#' => TrophyApiController::class,
      '#/' . ResourceNames::CHAMPIONSHIP . '/\d+/trophies/award#' => TrophyApiController::class,
      '#/' . ResourceNames::CHAMPIONSHIP . '#' => ChampionshipApiController::class,
      '#/' . ResourceNames::DRIVER_CATEGORY . '#' => DriverCategoryApiController::class,
      '#/' . ResourceNames::EVENT_CLASS . '#' => EventClassApiController::class,
      '#/' . ResourceNames::GAME_CONFIG . '#' => GameConfigApiController::class,
      '#/' . ResourceNames::GAME . '#' => GameApiController::class,
      '#/' . ResourceNames::RULE_SET_RULE . '#' => RuleSetRuleApiController::class,
      '#/' . ResourceNames::RULE_SET . '/\d+/rules#' => RuleSetRuleApiController::class,
      '#/' . ResourceNames::RULE_SET . '#' => RuleSetApiController::class,
      '#/' . ResourceNames::SCORING_SET_SCORE . '#' => ScoringSetScoreApiController::class,
      '#/' . ResourceNames::SCORING_SET . '/\d+/scores#' => ScoringSetScoreApiController::class,
      '#/' . ResourceNames::SCORING_SET . '#' => ScoringSetApiController::class,
      '#/' . ResourceNames::SERVER_SETTING . '#' => ServerSettingApiController::class,
      '#/' . ResourceNames::SERVER . '/\d+/settings#' => ServerSettingApiController::class,
      '#/' . ResourceNames::SERVER . '#' => ServerApiController::class,
      '#/' . ResourceNames::MEMBER . '#' => MemberApiController::class,
      '#/' . ResourceNames::MIGRATION . '#' => MigrationApiController::class,
      '#/' . ResourceNames::PROVISIONING . '#' => ProvisioningApiController::class,
      '#/' . ResourceNames::TEAM . '#' => TeamApiController::class,
    ];

    public static function init(): void {
      add_action('rest_api_init', [self::class, 'registerRoutes']);
    }

    public static function registerRoutes(): void {
      global $wp;

      if (!str_contains($wp->request, self::API_NAMESPACE)) {
        return;
      }

      $apiController = self::resolveController($wp->request);
      $apiController?->registerRoutes();
    }

    private static function resolveController(string $request): ?ApiController {
      foreach (self::$routeMap as $pattern => $controllerClass) {
        if (preg_match($pattern, $request)) {
          return new $controllerClass();
        }
      }

      return null;
    }

  }
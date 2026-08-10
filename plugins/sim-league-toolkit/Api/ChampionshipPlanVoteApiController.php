<?php

  namespace SLTK\Api;

  use SLTK\Core\Constants;
  use SLTK\Domain\ChampionshipPlan;
  use SLTK\Domain\Services\ChampionshipPlanVotingService;
  use WP_REST_Request;
  use WP_REST_Response;

  /**
   * Member-gated voting endpoints for Championship Plans. Unlike every other controller in the
   * plugin (all gated on manage_options via the HasGet/HasPost/etc traits), these routes must be
   * reachable by any logged-in member from the public front end, so they're registered directly
   * with a custom is_user_logged_in() permission callback rather than using the HTTP-verb traits.
   */
  class ChampionshipPlanVoteApiController extends ApiController {

    public function __construct() {
      parent::__construct(ResourceNames::CHAMPIONSHIP_PLAN);
    }

    public function registerRoutes(): void {
      $base = ResourceNames::CHAMPIONSHIP_PLAN . '/' . Constants::ROUTE_PATTERN_ID . '/vote';

      $this->registerRoute("$base/ballot", 'GET', [$this, 'canVote'], [$this, 'getBallot']);

      $this->registerRoute("$base/track-votes/(?P<trackId>\\d+)", 'POST', [$this, 'canVote'], [$this, 'castTrackVote']);
      $this->registerRoute("$base/track-votes/(?P<trackId>\\d+)", 'DELETE', [$this, 'canVote'], [$this, 'retractTrackVote']);
      $this->registerRoute("$base/track-favourites/(?P<trackId>\\d+)", 'POST', [$this, 'canVote'], [$this, 'addTrackFavourite']);
      $this->registerRoute("$base/track-favourites/(?P<trackId>\\d+)", 'DELETE', [$this, 'canVote'], [$this, 'removeTrackFavourite']);
      $this->registerRoute("$base/pick-tracks-for-me", 'POST', [$this, 'canVote'], [$this, 'pickTracksForMe']);

      $this->registerRoute("$base/car-votes/(?P<carId>\\d+)", 'POST', [$this, 'canVote'], [$this, 'castCarVote']);
      $this->registerRoute("$base/car-votes/(?P<carId>\\d+)", 'DELETE', [$this, 'canVote'], [$this, 'retractCarVote']);
      $this->registerRoute("$base/pick-cars-for-me", 'POST', [$this, 'canVote'], [$this, 'pickCarsForMe']);

      $this->registerRoute("$base/class-votes/(?P<planClassId>\\d+)", 'POST', [$this, 'canVote'], [$this, 'castClassVote']);
      $this->registerRoute("$base/class-votes/(?P<planClassId>\\d+)", 'DELETE', [$this, 'canVote'], [$this, 'retractClassVote']);
      $this->registerRoute("$base/suggest-class", 'POST', [$this, 'canVote'], [$this, 'suggestClass']);
    }

    public function canVote(): bool {
      return is_user_logged_in();
    }

    public function getBallot(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $planId = $this->getId($request);
        $plan = ChampionshipPlan::get($planId);
        if ($plan === null) {
          return ApiResponse::notFound('Championship plan');
        }

        $service = new ChampionshipPlanVotingService();
        $currentUserId = get_current_user_id();

        return ApiResponse::success([
          'plan' => $plan->toDto(),
          'tracks' => array_map(fn($t) => $t->toDto(), $service->getTrackTallies($planId, $currentUserId)),
          'cars' => array_map(fn($t) => $t->toDto(), $service->getCarTallies($planId, $currentUserId)),
          'classes' => array_map(fn($t) => $t->toDto(), $service->getClassTallies($planId, $currentUserId)),
        ]);
      });
    }

    public function castTrackVote(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        (new ChampionshipPlanVotingService())->castTrackVote($this->getId($request), (int)$request->get_param('trackId'), get_current_user_id());

        return ApiResponse::noContent();
      });
    }

    public function retractTrackVote(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        (new ChampionshipPlanVotingService())->retractTrackVote($this->getId($request), (int)$request->get_param('trackId'), get_current_user_id());

        return ApiResponse::noContent();
      });
    }

    public function addTrackFavourite(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        (new ChampionshipPlanVotingService())->setTrackFavourite($this->getId($request), (int)$request->get_param('trackId'), get_current_user_id(), true);

        return ApiResponse::noContent();
      });
    }

    public function removeTrackFavourite(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        (new ChampionshipPlanVotingService())->setTrackFavourite($this->getId($request), (int)$request->get_param('trackId'), get_current_user_id(), false);

        return ApiResponse::noContent();
      });
    }

    public function pickTracksForMe(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $tallies = (new ChampionshipPlanVotingService())->pickTracksForMe($this->getId($request), get_current_user_id());

        return ApiResponse::success(array_map(fn($t) => $t->toDto(), $tallies));
      });
    }

    public function castCarVote(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        (new ChampionshipPlanVotingService())->castCarVote($this->getId($request), (int)$request->get_param('carId'), get_current_user_id());

        return ApiResponse::noContent();
      });
    }

    public function retractCarVote(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        (new ChampionshipPlanVotingService())->retractCarVote($this->getId($request), (int)$request->get_param('carId'), get_current_user_id());

        return ApiResponse::noContent();
      });
    }

    public function pickCarsForMe(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $tallies = (new ChampionshipPlanVotingService())->pickCarsForMe($this->getId($request), get_current_user_id());

        return ApiResponse::success(array_map(fn($t) => $t->toDto(), $tallies));
      });
    }

    public function castClassVote(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        (new ChampionshipPlanVotingService())->castClassVote($this->getId($request), (int)$request->get_param('planClassId'), get_current_user_id());

        return ApiResponse::noContent();
      });
    }

    public function retractClassVote(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        (new ChampionshipPlanVotingService())->retractClassVote($this->getId($request), (int)$request->get_param('planClassId'), get_current_user_id());

        return ApiResponse::noContent();
      });
    }

    public function suggestClass(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $planClassId = (new ChampionshipPlanVotingService())->suggestClass($this->getId($request), get_current_user_id(), $this->getParams($request));

        return ApiResponse::created($planClassId);
      });
    }
  }

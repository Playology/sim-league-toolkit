<?php

  namespace SLTK\Api;

  use SLTK\Api\Traits\HasPost;
  use SLTK\Domain\Services\ChampionshipBuilderService;
  use SLTK\Domain\ValueObjects\ChampionshipBuilderPlan;
  use WP_REST_Request;
  use WP_REST_Response;

  class ChampionshipBuilderApiController extends ApiController {
    use HasPost;

    public function __construct() {
      parent::__construct(ResourceNames::CHAMPIONSHIP_BUILDER);
    }

    public function registerRoutes(): void {
      $this->registerPostRoute();
    }

    protected function onPost(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $plan = ChampionshipBuilderPlan::fromArray($this->getParams($request));
        $service = new ChampionshipBuilderService();

        $errors = $service->validate($plan);
        if (!empty($errors)) {
          return ApiResponse::validationFailed($errors);
        }

        $championship = $service->build($plan);

        return ApiResponse::created($championship->getId());
      });
    }
  }

<?php

  namespace SLTK\Api;

  use DateTime;
  use DateTimeInterface;
  use DateTimeZone;
  use SLTK\Api\Traits\HasDelete;
  use SLTK\Api\Traits\HasGet;
  use SLTK\Api\Traits\HasGetById;
  use SLTK\Api\Traits\HasPost;
  use SLTK\Api\Traits\HasPut;
  use SLTK\Core\BannerImageProvider;
  use SLTK\Core\Constants;
  use SLTK\Domain\Championship;
  use SLTK\Domain\ChampionshipEvent;
  use WP_REST_Request;
  use WP_REST_Response;

  class ChampionshipEventApiController extends ApiController {
    use HasDelete, HasGet, HasGetById, HasPost, HasPut;

    public function __construct() {
      parent::__construct(ResourceNames::CHAMPIONSHIP_EVENT);
    }

    public function registerRoutes(): void {
      $this->registerDeleteRoute();
      $this->registerRoute(ResourceNames::CHAMPIONSHIP . '/' . Constants::ROUTE_PATTERN_ID . '/events', 'GET', [$this, 'canGet'], [$this, 'get']);
      $this->registerGetByIdRoute();
      $this->registerRoute(ResourceNames::CHAMPIONSHIP . '/' . Constants::ROUTE_PATTERN_ID . '/events', 'POST', [$this, 'canPost'], [$this, 'post']);
      $this->registerPutRoute();
    }

    protected function onDelete(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        Championship::deleteEvent($this->getId($request));

        return ApiResponse::noContent();
      });
    }

    protected function onGet(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $data = Championship::listEvents($this->getId($request));

        return ApiResponse::success(
          array_map(fn($s) => $s->toDto(), $data)
        );
      });
    }

    protected function onGetById(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $data = Championship::getEventById($this->getId($request));

        if ($data === null) {
          return ApiResponse::notFound('ChampionshipEvent');
        }

        return ApiResponse::success($data->toDto());
      });
    }

    protected function onPost(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $entity = new ChampionshipEvent();
        $entity->setChampionshipId($this->getId($request));

        $this->hydrateFromRequest($entity, $request);

        if (empty($entity->getBannerImageUrl())) {
          $entity->setBannerImageUrl(BannerImageProvider::getRandomBannerImageUrl());
        }

        $entity->save();

        return ApiResponse::created($entity->getId());
      });
    }

    protected function onPut(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $entity = ChampionshipEvent::get($this->getId($request));

        if ($entity === null) {
          return ApiResponse::notFound('ChampionshipEvent');
        }

        $this->hydrateFromRequest($entity, $request);

        $params = $this->getParams($request);
        $entity->setIsActive((bool)($params['isActive'] ?? false));

        $entity->save();

        return ApiResponse::noContent();
      });
    }

    private function hydrateFromRequest(ChampionshipEvent $entity, WP_REST_Request $request): void {
      $params = $this->getParams($request);

      $entity->setName($params['name']);
      $entity->setTrackId((int)$params['trackId']);

      $startDateTime = DateTime::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, $params['startDateTime'], new DateTimeZone('UTC'));
      $entity->setStartDateTime($startDateTime ?: new \DateTime('now', new DateTimeZone('UTC')));

      $entity->setTrackLayoutId(isset($params['trackLayoutId']) && $params['trackLayoutId'] ? (int)$params['trackLayoutId'] : null);
      $entity->setTrackMasterCarId(isset($params['trackMasterCarId']) && $params['trackMasterCarId'] ? (int)$params['trackMasterCarId'] : null);
    }
  }

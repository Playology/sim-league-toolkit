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
  use SLTK\Core\Constants;
  use SLTK\Core\Enums\ChampionshipType;
  use SLTK\Core\Enums\PlanStatus;
  use SLTK\Domain\ChampionshipPlan;
  use SLTK\Domain\EventClass;
  use SLTK\Domain\Services\ChampionshipPlanVotingService;
  use SLTK\Domain\ValueObjects\PlanTrackFilter;
  use SLTK\Database\Repositories\CarRepository;
  use SLTK\Database\Repositories\EventClassesRepository;
  use SLTK\Database\Repositories\RepositoryBase;
  use SLTK\Database\Repositories\TrackRepository;
  use WP_REST_Request;
  use WP_REST_Response;

  class ChampionshipPlanApiController extends ApiController {
    use HasDelete, HasGet, HasGetById, HasPost, HasPut;

    public function __construct() {
      parent::__construct(ResourceNames::CHAMPIONSHIP_PLAN);
    }

    public function registerRoutes(): void {
      $this->registerDeleteRoute();
      $this->registerGetRoute();
      $this->registerGetByIdRoute();
      $this->registerPostRoute();
      $this->registerPutRoute();

      $base = ResourceNames::CHAMPIONSHIP_PLAN . '/' . Constants::ROUTE_PATTERN_ID;

      $this->registerRoute("$base/created-championship", 'POST', [$this, 'canPost'], [$this, 'setCreatedChampionship']);

      $this->registerRoute("$base/tallies", 'GET', [$this, 'canGetById'], [$this, 'getTallies']);

      $this->registerRoute("$base/tracks/available", 'GET', [$this, 'canGetById'], [$this, 'listAvailableTracks']);
      $this->registerRoute("$base/tracks/bulk", 'POST', [$this, 'canPost'], [$this, 'addTracksBulk']);
      $this->registerRoute("$base/tracks", 'POST', [$this, 'canPost'], [$this, 'addTrack']);
      $this->registerRoute("$base/tracks/(?P<trackId>\\d+)", 'DELETE', [$this, 'canDelete'], [$this, 'removeTrack']);

      $this->registerRoute("$base/cars/available", 'GET', [$this, 'canGetById'], [$this, 'listAvailableCars']);
      $this->registerRoute("$base/cars", 'POST', [$this, 'canPost'], [$this, 'addCar']);
      $this->registerRoute("$base/cars/(?P<carId>\\d+)", 'DELETE', [$this, 'canDelete'], [$this, 'removeCar']);

      $this->registerRoute("$base/classes/available", 'GET', [$this, 'canGetById'], [$this, 'listAvailableClasses']);
      $this->registerRoute("$base/classes", 'POST', [$this, 'canPost'], [$this, 'addClass']);
      $this->registerRoute("$base/classes/(?P<planClassId>\\d+)", 'DELETE', [$this, 'canDelete'], [$this, 'removeClass']);
    }

    protected function onDelete(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        ChampionshipPlan::delete($this->getId($request));

        return ApiResponse::noContent();
      });
    }

    protected function onGet(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $data = ChampionshipPlan::list();

        return ApiResponse::success(array_map(fn($p) => $p->toDto(), $data));
      });
    }

    protected function onGetById(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $data = ChampionshipPlan::get($this->getId($request));

        if ($data === null) {
          return ApiResponse::notFound('Championship plan');
        }

        return ApiResponse::success($data->toDto());
      });
    }

    protected function onPost(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $entity = $this->hydrateFromRequest(new ChampionshipPlan(), $request);
        $entity->save();

        return ApiResponse::created($entity->getId());
      });
    }

    protected function onPut(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $entity = ChampionshipPlan::get($this->getId($request));

        if ($entity === null) {
          return ApiResponse::notFound('Championship plan');
        }

        $entity = $this->hydrateFromRequest($entity, $request);
        $entity->save();

        return ApiResponse::noContent();
      });
    }

    public function setCreatedChampionship(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $entity = ChampionshipPlan::get($this->getId($request));

        if ($entity === null) {
          return ApiResponse::notFound('Championship plan');
        }

        $params = $this->getParams($request);
        $entity->setCreatedChampionshipId((int)$params['championshipId']);
        $entity->save();

        return ApiResponse::noContent();
      });
    }

    public function getTallies(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $planId = $this->getId($request);
        $service = new ChampionshipPlanVotingService();
        $currentUserId = get_current_user_id();

        return ApiResponse::success([
          'tracks' => array_map(fn($t) => $t->toDto(), $service->getTrackTallies($planId, $currentUserId)),
          'cars' => array_map(fn($t) => $t->toDto(), $service->getCarTallies($planId, $currentUserId)),
          'classes' => array_map(fn($t) => $t->toDto(), $service->getClassTallies($planId, $currentUserId)),
        ]);
      });
    }

    public function listAvailableTracks(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $planId = $this->getId($request);
        $plan = ChampionshipPlan::get($planId);
        if ($plan === null) {
          return ApiResponse::notFound('Championship plan');
        }

        $filter = PlanTrackFilter::fromArray($request->get_params());
        $data = TrackRepository::listAvailableForPlan($planId, $plan->getGameId(), $filter);

        return ApiResponse::success(array_map(fn($row) => [
          'trackId' => (int)$row->id,
          'trackName' => $row->shortName,
        ], $data));
      });
    }

    public function addTrack(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $params = $this->getParams($request);

        ChampionshipPlan::addTrack($this->getId($request), (int)$params['trackId']);

        return ApiResponse::noContent();
      });
    }

    public function addTracksBulk(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $planId = $this->getId($request);
        $params = $this->getParams($request);
        $trackIds = array_map('intval', (array)($params['trackIds'] ?? []));

        RepositoryBase::transaction(function () use ($planId, $trackIds) {
          foreach ($trackIds as $trackId) {
            ChampionshipPlan::addTrack($planId, $trackId);
          }
        });

        return ApiResponse::noContent();
      });
    }

    public function removeTrack(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        ChampionshipPlan::removeTrack($this->getId($request), (int)$request->get_param('trackId'));

        return ApiResponse::noContent();
      });
    }

    public function listAvailableCars(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $planId = $this->getId($request);
        $plan = ChampionshipPlan::get($planId);
        if ($plan === null) {
          return ApiResponse::notFound('Championship plan');
        }

        $data = CarRepository::listAvailableForPlan($planId, $plan->getGameId());

        return ApiResponse::success(array_map(fn($row) => [
          'carId' => (int)$row->id,
          'carName' => $row->name,
          'carClass' => $row->carClass,
        ], $data));
      });
    }

    public function addCar(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $params = $this->getParams($request);

        ChampionshipPlan::addCar($this->getId($request), (int)$params['carId']);

        return ApiResponse::noContent();
      });
    }

    public function removeCar(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        ChampionshipPlan::removeCar($this->getId($request), (int)$request->get_param('carId'));

        return ApiResponse::noContent();
      });
    }

    public function listAvailableClasses(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $planId = $this->getId($request);
        $plan = ChampionshipPlan::get($planId);
        if ($plan === null) {
          return ApiResponse::notFound('Championship plan');
        }

        $data = EventClassesRepository::listAvailableForPlan($planId, $plan->getGameId());

        return ApiResponse::success(array_map(fn($row) => EventClass::fromStdClass($row)->toDto(), $data));
      });
    }

    public function addClass(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $params = $this->getParams($request);

        $planClassId = ChampionshipPlan::addClass($this->getId($request), [
          'eventClassId' => (int)$params['eventClassId'],
          'name' => (string)($params['name'] ?? ''),
          'carClass' => (string)($params['carClass'] ?? ''),
          'driverCategoryId' => null,
          'isSingleCarClass' => false,
          'singleCarId' => null,
          'suggestedByUserId' => null,
        ]);

        return ApiResponse::created($planClassId);
      });
    }

    public function removeClass(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        ChampionshipPlan::removeClass($this->getId($request), (int)$request->get_param('planClassId'));

        return ApiResponse::noContent();
      });
    }

    private function hydrateFromRequest(ChampionshipPlan $entity, WP_REST_Request $request): ChampionshipPlan {
      $params = $this->getParams($request);

      $entity->setName($params['name']);
      $entity->setDescription($params['description']);
      $entity->setGameId((int)$params['gameId']);
      $entity->setPlatformId((int)$params['platformId']);
      $entity->setChampionshipType(ChampionshipType::tryFrom($params['championshipType']) ?? ChampionshipType::Standard);
      $entity->setStatus(PlanStatus::tryFrom($params['status']) ?? PlanStatus::Draft);
      $startDate = DateTime::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, $params['startDate'], new DateTimeZone('UTC'));
      $entity->setStartDate($startDate);
      $entity->setClassesFixed((bool)($params['classesFixed'] ?? true));
      $entity->setMaxTrackVotesPerUser((int)($params['maxTrackVotesPerUser'] ?? 1));
      $entity->setMaxCarVotesPerUser((int)($params['maxCarVotesPerUser'] ?? 1));
      $entity->setMaxCarsPerClass((int)($params['maxCarsPerClass'] ?? 1));

      return $entity;
    }
  }

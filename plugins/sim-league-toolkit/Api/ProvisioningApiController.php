<?php

  namespace SLTK\Api;

  use SLTK\Core\Constants;
  use SLTK\Core\Enums\ProvisioningState;
  use SLTK\Provisioning\ProvisionableItemCatalog;
  use SLTK\Provisioning\ProvisioningItemStatus;
  use SLTK\Provisioning\SiteProvisioningService;
  use SLTK\Provisioning\SiteSuitabilityChecker;
  use WP_REST_Request;
  use WP_REST_Response;

  class ProvisioningApiController extends ApiController {

    public function __construct() {
      parent::__construct(ResourceNames::PROVISIONING);
    }

    public function registerRoutes(): void {
      $this->registerRoute('/' . ResourceNames::PROVISIONING . '/status', 'GET', [$this, 'canManage'], [$this, 'status']);
      $this->registerRoute('/' . ResourceNames::PROVISIONING . '/(?P<itemKey>[a-zA-Z0-9:_-]+)/provision', 'POST', [$this, 'canManage'], [$this, 'provisionItem']);
      $this->registerRoute('/' . ResourceNames::PROVISIONING . '/(?P<itemKey>[a-zA-Z0-9:_-]+)/remove', 'POST', [$this, 'canManage'], [$this, 'removeItem']);
    }

    public function canManage(): bool {
      return current_user_can(Constants::MANAGE_OPTIONS_PERMISSION);
    }

    public function provisionItem(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $item = ProvisionableItemCatalog::find((string)$request->get_param('itemKey'));

        if ($item === null) {
          return ApiResponse::notFound('Provisioning item');
        }

        $params = $this->getParams($request);
        SiteProvisioningService::provision($item->itemKey, (string)($params['action'] ?? ''));

        return ApiResponse::success(SiteProvisioningService::analyseItem($item)->toDto());
      });
    }

    public function removeItem(WP_REST_Request $request): WP_REST_Response {
      return $this->execute(function () use ($request) {
        $item = ProvisionableItemCatalog::find((string)$request->get_param('itemKey'));

        if ($item === null) {
          return ApiResponse::notFound('Provisioning item');
        }

        $params = $this->getParams($request);
        $mode = (string)($params['mode'] ?? '');
        $confirmDrift = (bool)($params['confirmDrift'] ?? false);

        if ($mode === 'delete') {
          $status = SiteProvisioningService::analyseItem($item);

          if ($status->state === ProvisioningState::Drifted && !$confirmDrift) {
            return ApiResponse::badRequest(
              'This item has been edited since it was provisioned. Confirm removal to proceed.',
              ['requiresDriftConfirmation' => true]
            );
          }
        }

        SiteProvisioningService::remove($item->itemKey, $mode, $confirmDrift);

        return ApiResponse::success(SiteProvisioningService::analyseItem($item)->toDto());
      });
    }

    public function status(): WP_REST_Response {
      return $this->execute(function () {
        return ApiResponse::success([
          'warnings' => SiteSuitabilityChecker::check(),
          'items' => array_map(fn(ProvisioningItemStatus $status) => $status->toDto(), SiteProvisioningService::analyse()),
        ]);
      });
    }
  }

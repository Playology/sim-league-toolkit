<?php

  namespace SLTK\Provisioning;

  use InvalidArgumentException;
  use SLTK\Core\Enums\ProvisioningKind;
  use SLTK\Core\Enums\ProvisioningState;
  use SLTK\Database\Repositories\ProvisionedItemsRepository;
  use WP_Post;

  class SiteProvisioningService {
    private const string ACTION_CREATE = 'create';
    private const string ACTION_ADOPT = 'adopt';
    private const string ACTION_OVERWRITE = 'overwrite';
    private const string REMOVAL_MODE_DELETE = 'delete';
    private const string REMOVAL_MODE_STOP_TRACKING = 'stopTracking';

    /**
     * @return ProvisioningItemStatus[]
     */
    public static function analyse(): array {
      return array_map([self::class, 'analyseItem'], ProvisionableItemCatalog::all());
    }

    public static function analyseItem(ProvisionableItem $item): ProvisioningItemStatus {
      $tracked = ProvisionedItemsRepository::getByItemKey($item->itemKey);

      if ($tracked === null) {
        return self::analyseUntrackedItem($item);
      }

      $trackedPost = get_post((int)$tracked->targetId);

      if ($trackedPost === null) {
        // The page/menu was deleted outside our flow — forget the stale tracking row and
        // re-analyse as if it had never been provisioned.
        ProvisionedItemsRepository::deleteByItemKey($item->itemKey);

        return self::analyseUntrackedItem($item);
      }

      $currentHash = hash('sha256', $trackedPost->post_content);
      $state = $currentHash === $tracked->contentHash ? ProvisioningState::Provisioned : ProvisioningState::Drifted;

      return new ProvisioningItemStatus(
        $item->itemKey, $item->label, $item->kind, $state,
        (int)$trackedPost->ID, $trackedPost->post_title, $trackedPost->post_modified
      );
    }

    public static function provision(string $itemKey, string $action): void {
      $item = self::requireItem($itemKey);
      $status = self::analyseItem($item);

      if ($action === self::ACTION_CREATE) {
        if ($status->state !== ProvisioningState::NotProvisioned) {
          throw new InvalidArgumentException('This item cannot be created — it is already provisioned or a conflict exists.');
        }

        self::create($item);

        return;
      }

      if ($action === self::ACTION_ADOPT) {
        if ($status->state !== ProvisioningState::ConflictFound) {
          throw new InvalidArgumentException('This item cannot be adopted — no existing conflict was found for it.');
        }

        self::adopt($item, $status->existingId);

        return;
      }

      if ($action === self::ACTION_OVERWRITE) {
        // Valid whenever a real post already exists to overwrite — a found conflict, or an
        // already-tracked item (provisioned or drifted) whose generated content should be
        // refreshed, e.g. after a pattern's markup changes. Not valid from NotProvisioned, since
        // there's nothing yet to overwrite — that's what create() is for. Home is excluded once
        // tracked: its generated content is deliberately blank (Mike edits it by hand once
        // provisioned), so "refreshing" it would just wipe out real content for no benefit —
        // only allowed for Home while still resolving a found conflict.
        $isBlankHomeRefresh = $item->itemKey === ProvisionableItemCatalog::homeItemKey()
          && $status->state !== ProvisioningState::ConflictFound;

        if ($status->existingId === null || $isBlankHomeRefresh) {
          throw new InvalidArgumentException('This item cannot be overwritten.');
        }

        self::overwrite($item, $status->existingId);

        return;
      }

      throw new InvalidArgumentException("Unknown provisioning action: {$action}");
    }

    public static function remove(string $itemKey, string $mode, bool $confirmDrift = false): void {
      $item = self::requireItem($itemKey);
      $tracked = ProvisionedItemsRepository::getByItemKey($itemKey);

      if ($tracked === null) {
        throw new InvalidArgumentException('This item is not currently provisioned.');
      }

      if ($mode === self::REMOVAL_MODE_STOP_TRACKING) {
        ProvisionedItemsRepository::deleteByItemKey($itemKey);

        return;
      }

      if ($mode !== self::REMOVAL_MODE_DELETE) {
        throw new InvalidArgumentException("Unknown removal mode: {$mode}");
      }

      $status = self::analyseItem($item);
      if ($status->state === ProvisioningState::Drifted && !$confirmDrift) {
        throw new InvalidArgumentException('This item has been edited since it was provisioned — confirm removal to proceed.');
      }

      $targetId = (int)$tracked->targetId;
      wp_delete_post($targetId, true);
      ProvisionedItemsRepository::deleteByItemKey($itemKey);

      if ($itemKey === ProvisionableItemCatalog::homeItemKey()) {
        self::revertFrontPageIfPointingAt($targetId);
      }
    }

    private static function adopt(ProvisionableItem $item, int $existingId): void {
      $existingPost = get_post($existingId);
      $contentHash = hash('sha256', $existingPost->post_content);

      ProvisionedItemsRepository::recordProvisioned($item->itemKey, $existingId, $item->kind, $contentHash, 'adopted');

      if ($item->itemKey === ProvisionableItemCatalog::homeItemKey()) {
        self::setAsFrontPage($existingId);
      }
    }

    private static function overwrite(ProvisionableItem $item, int $existingId): void {
      $content = self::generatedContent($item);

      self::updatePost(['ID' => $existingId, 'post_content' => $content]);
      ProvisionedItemsRepository::recordProvisioned($item->itemKey, $existingId, $item->kind, self::savedContentHash($existingId), 'overwrote');

      if ($item->itemKey === ProvisionableItemCatalog::homeItemKey()) {
        self::setAsFrontPage($existingId);
      }
    }

    /**
     * WordPress runs post_content through save filters (kses sanitisation, wptexturize-adjacent
     * cleanup, block validation, etc.), so the string actually stored can differ from what was
     * passed to wp_insert_post()/wp_update_post(). Hashing the saved value (not the pre-save
     * local variable) is what keeps analyseItem()'s drift check from firing immediately after a
     * fresh create/overwrite.
     */
    private static function savedContentHash(int $postId): string {
      return hash('sha256', get_post($postId)->post_content);
    }

    private static function generatedContent(ProvisionableItem $item): string {
      return $item->kind === ProvisioningKind::Navigation ? self::buildNavigationLinks() : $item->content();
    }

    private static function analyseUntrackedItem(ProvisionableItem $item): ProvisioningItemStatus {
      if ($item->itemKey === ProvisionableItemCatalog::homeItemKey()) {
        return self::analyseUntrackedHomeItem($item);
      }

      $existing = $item->kind === ProvisioningKind::Navigation
        ? self::mostRecentNavigationMenu()
        : get_page_by_path($item->slug);

      if ($existing === null) {
        return new ProvisioningItemStatus($item->itemKey, $item->label, $item->kind, ProvisioningState::NotProvisioned);
      }

      return new ProvisioningItemStatus(
        $item->itemKey, $item->label, $item->kind, ProvisioningState::ConflictFound,
        (int)$existing->ID, $existing->post_title, $existing->post_modified
      );
    }

    private static function analyseUntrackedHomeItem(ProvisionableItem $item): ProvisioningItemStatus {
      $existingPageAtSlug = get_page_by_path('home');
      if ($existingPageAtSlug !== null) {
        return new ProvisioningItemStatus(
          $item->itemKey, $item->label, $item->kind, ProvisioningState::ConflictFound,
          (int)$existingPageAtSlug->ID, $existingPageAtSlug->post_title, $existingPageAtSlug->post_modified
        );
      }

      $currentFrontPageId = get_option('show_on_front') === 'page' ? (int)get_option('page_on_front') : 0;
      $currentFrontPage = $currentFrontPageId > 0 ? get_post($currentFrontPageId) : null;

      if ($currentFrontPage !== null) {
        return new ProvisioningItemStatus(
          $item->itemKey, $item->label, $item->kind, ProvisioningState::ConflictFound,
          (int)$currentFrontPage->ID, $currentFrontPage->post_title, $currentFrontPage->post_modified
        );
      }

      return new ProvisioningItemStatus($item->itemKey, $item->label, $item->kind, ProvisioningState::NotProvisioned);
    }

    private static function buildNavigationLinks(): string {
      $links = '';

      foreach (ProvisionableItemCatalog::pageItems() as $pageItem) {
        $tracked = ProvisionedItemsRepository::getByItemKey($pageItem->itemKey);
        $page = $tracked !== null ? get_post((int)$tracked->targetId) : get_page_by_path($pageItem->slug);

        if ($page === null) {
          continue;
        }

        $links .= self::navigationLinkBlock($page, $pageItem->label);
      }

      return $links;
    }

    private static function create(ProvisionableItem $item): void {
      if ($item->kind === ProvisioningKind::Navigation) {
        self::createNavigation($item);

        return;
      }

      self::createPage($item);
    }

    private static function createNavigation(ProvisionableItem $item): void {
      $content = self::generatedContent($item);

      $postId = self::insertPost([
        'post_title' => 'Primary Menu',
        'post_status' => 'publish',
        'post_type' => 'wp_navigation',
        'post_content' => $content,
      ]);

      ProvisionedItemsRepository::recordProvisioned($item->itemKey, $postId, $item->kind, self::savedContentHash($postId), 'created');
    }

    private static function createPage(ProvisionableItem $item): void {
      $content = self::generatedContent($item);

      $postId = self::insertPost([
        'post_title' => $item->label,
        'post_name' => $item->slug,
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_content' => $content,
      ]);

      ProvisionedItemsRepository::recordProvisioned($item->itemKey, $postId, $item->kind, self::savedContentHash($postId), 'created');

      if ($item->itemKey === ProvisionableItemCatalog::homeItemKey()) {
        self::setAsFrontPage($postId);
      }
    }

    private static function insertPost(array $postArgs): int {
      $result = wp_insert_post($postArgs, true);

      if (is_wp_error($result)) {
        throw new InvalidArgumentException($result->get_error_message());
      }

      return $result;
    }

    private static function updatePost(array $postArgs): void {
      $result = wp_update_post($postArgs, true);

      if (is_wp_error($result)) {
        throw new InvalidArgumentException($result->get_error_message());
      }
    }

    private static function mostRecentNavigationMenu(): ?WP_Post {
      $menus = get_posts([
        'post_type' => 'wp_navigation',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'orderby' => 'date',
        'order' => 'DESC',
      ]);

      return $menus[0] ?? null;
    }

    private static function navigationLinkBlock(WP_Post $page, string $label): string {
      $attributes = wp_json_encode([
        'label' => $label,
        'type' => 'page',
        'id' => $page->ID,
        'url' => get_permalink($page),
        'kind' => 'post-type',
      ]);

      return sprintf("<!-- wp:navigation-link %s /-->\n", $attributes);
    }

    private static function requireItem(string $itemKey): ProvisionableItem {
      $item = ProvisionableItemCatalog::find($itemKey);

      if ($item === null) {
        throw new InvalidArgumentException("Unknown provisioning item: {$itemKey}");
      }

      return $item;
    }

    private static function revertFrontPageIfPointingAt(int $postId): void {
      if ((int)get_option('page_on_front') !== $postId) {
        return;
      }

      update_option('show_on_front', 'posts');
      update_option('page_on_front', 0);
    }

    private static function setAsFrontPage(int $postId): void {
      update_option('show_on_front', 'page');
      update_option('page_on_front', $postId);
    }
  }

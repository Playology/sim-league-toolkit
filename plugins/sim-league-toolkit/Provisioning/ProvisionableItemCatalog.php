<?php

  namespace SLTK\Provisioning;

  use SLTK\Blocks\Patterns\CurrentAndPastTabsPattern;
  use SLTK\Core\Enums\ProvisioningKind;

  class ProvisionableItemCatalog {
    public static function all(): array {
      return [
        new ProvisionableItem('page-home', __('Home', 'sim-league-toolkit'), ProvisioningKind::Page, 'home'),
        new ProvisionableItem('page-championships', __('Championships', 'sim-league-toolkit'), ProvisioningKind::Page, 'championships', fn() => CurrentAndPastTabsPattern::forChampionships()),
        new ProvisionableItem('page-events', __('Events', 'sim-league-toolkit'), ProvisioningKind::Page, 'events', fn() => CurrentAndPastTabsPattern::forEvents()),
        new ProvisionableItem('navigation-primary', __('Primary Navigation Menu', 'sim-league-toolkit'), ProvisioningKind::Navigation),
      ];
    }

    public static function find(string $itemKey): ?ProvisionableItem {
      foreach (self::all() as $item) {
        if ($item->itemKey === $itemKey) {
          return $item;
        }
      }

      return null;
    }

    public static function homeItemKey(): string {
      return 'page-home';
    }

    public static function pageItems(): array {
      return array_filter(self::all(), fn(ProvisionableItem $item) => $item->kind === ProvisioningKind::Page);
    }
  }

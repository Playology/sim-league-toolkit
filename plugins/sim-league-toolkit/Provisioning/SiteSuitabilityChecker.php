<?php

  namespace SLTK\Provisioning;

  class SiteSuitabilityChecker {
    private const array BUILDER_PLUGIN_LABELS_BY_SLUG = [
      'elementor/elementor.php' => 'Elementor',
      'js_composer/js_composer.php' => 'WPBakery Page Builder',
      'beaver-builder-lite-version/fl-builder.php' => 'Beaver Builder',
      'bb-plugin/fl-builder.php' => 'Beaver Builder',
      'oxygen/functions.php' => 'Oxygen Builder',
    ];

    private const array BUILDER_THEME_NAME_NEEDLES = ['Divi', 'Bricks', 'Oxygen'];

    private const int PUBLISHED_PAGE_COUNT_WARNING_THRESHOLD = 5;

    /**
     * @return string[]
     */
    public static function check(): array {
      $warnings = [];

      $activeBuilderPlugin = self::activeBuilderPluginLabel();
      if ($activeBuilderPlugin !== null) {
        $warnings[] = sprintf(
          __('The %s page builder plugin is active — provisioning may conflict with content it manages.', 'sim-league-toolkit'),
          $activeBuilderPlugin
        );
      }

      $activeBuilderTheme = self::activeBuilderThemeName();
      if ($activeBuilderTheme !== null) {
        $warnings[] = sprintf(
          __('The active theme (%s) is a page-builder theme — provisioned pages may not render as expected.', 'sim-league-toolkit'),
          $activeBuilderTheme
        );
      }

      $publishedPageCount = self::publishedPageCount();
      if ($publishedPageCount > self::PUBLISHED_PAGE_COUNT_WARNING_THRESHOLD) {
        $warnings[] = sprintf(
          __('This site already has %d published pages — review carefully before provisioning to avoid duplicating existing content.', 'sim-league-toolkit'),
          $publishedPageCount
        );
      }

      if (!wp_is_block_theme()) {
        $warnings[] = __('The active theme is not a block theme — the Navigation block and SLTK patterns may not render as intended.', 'sim-league-toolkit');
      }

      return $warnings;
    }

    private static function activeBuilderPluginLabel(): ?string {
      if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
      }

      foreach (self::BUILDER_PLUGIN_LABELS_BY_SLUG as $slug => $label) {
        if (is_plugin_active($slug)) {
          return $label;
        }
      }

      return null;
    }

    private static function activeBuilderThemeName(): ?string {
      $activeThemeName = wp_get_theme()->get('Name');

      foreach (self::BUILDER_THEME_NAME_NEEDLES as $needle) {
        if (stripos($activeThemeName, $needle) !== false) {
          return $activeThemeName;
        }
      }

      return null;
    }

    private static function publishedPageCount(): int {
      return (int)wp_count_posts('page')->publish;
    }
  }

<?php

  /**
   * Generates TypeScript enum definitions from PHP enums
   *
   * Run with: php scripts/generate-enums.php
   */

require_once __DIR__ . '/script-autoloader.php';

  use SLTK\Core\Enums\ChampionshipType;
  use SLTK\Core\Enums\EventType;
  use SLTK\Core\Enums\EventSessionType;
  use SLTK\Core\Enums\GameKey;
  use SLTK\Core\Enums\PlanStatus;
  use SLTK\Core\Enums\ResultStatus;
  use SLTK\Core\Enums\TrophyAwardType;
  use SLTK\Core\Enums\TrophyScope;

  $enums = [
    'ChampionshipType' => ChampionshipType::class,
    'EventType' => EventType::class,
    'SessionType' => EventSessionType::class,
    'GameKey' => GameKey::class,
    'PlanStatus' => PlanStatus::class,
    'ResultStatus' => ResultStatus::class,
    'TrophyAwardType' => TrophyAwardType::class,
    'TrophyScope' => TrophyScope::class,
  ];

  $output = "// Auto-generated from PHP enums - do not edit manually\n";
  $output .= '// Generated: ' . date('Y-m-d H:i:s') . "\n\n";

  foreach ($enums as $name => $class) {
    $cases = $class::cases();

    // Generate const object
    $output = "import {__} from '@wordpress/i18n';\n\n";
    $output .= "export const {$name} = {\n";
    foreach ($cases as $case) {
      $constName = strtoupper(preg_replace('/(?<!^)[A-Z]/', '_$0', $case->name));
      $output .= "    {$constName}: '{$case->value}',\n";
    }
    $output .= "} as const;\n\n";

    // Generate type
    $output .= "export type {$name} = typeof {$name}[keyof typeof {$name}];\n\n";

    // Generate labels
    $output .= "export const {$name}Labels: Record<{$name}, string> = {\n";
    foreach ($cases as $case) {
      $output .= "    '{$case->value}': __('{$case->label()}', 'sltk-league-toolkit'),\n";
    }
    $output .= "};\n\n";

    // Generate options array for select inputs
    $output .= "export const {$name}Options = [\n";
    foreach ($cases as $case) {
      $output .= "    { value: '{$case->value}', label: __('{$case->label()}', 'sltk-league-toolkit') },\n";
    }
    $output .= "] as const;\n\n";

    $outputPath = __DIR__ . '/../src/enums/generated/' . $name . '.ts';
    $outputDir = dirname($outputPath);

    if (!is_dir($outputDir)) {
      mkdir($outputDir, 0755, true);
    }

    file_put_contents($outputPath, $output);
  }

  echo "Generated: {$outputPath}\n";
  echo 'Enums processed: ' . implode(', ', array_keys($enums)) . "\n";
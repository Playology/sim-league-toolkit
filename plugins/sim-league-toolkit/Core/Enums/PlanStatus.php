<?php

  namespace SLTK\Core\Enums;

  enum PlanStatus: string
  {
    case Draft = 'draft';
    case Open = 'open';
    case Closed = 'closed';

    public function label(): string
    {
      return match($this)
      {
        self::Draft => 'Draft',
        self::Open => 'Open',
        self::Closed => 'Closed',
      };
    }

    public static function toArray(): array
    {
      return array_map(
        fn($case) => [
          'id' => $case->value,
          'name' => $case->label()
        ],
        self::cases()
      );
    }
  }

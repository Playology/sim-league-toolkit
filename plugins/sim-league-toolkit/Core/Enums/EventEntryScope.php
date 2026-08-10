<?php

  namespace SLTK\Core\Enums;

  enum EventEntryScope: string
  {
    case StandaloneEvent = 'standaloneEvent';
    case Championship = 'championship';

    public function label(): string
    {
      return match($this)
      {
        self::StandaloneEvent => 'Standalone Event',
        self::Championship => 'Championship',
      };
    }
  }

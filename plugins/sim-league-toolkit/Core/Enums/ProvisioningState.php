<?php

  namespace SLTK\Core\Enums;

  enum ProvisioningState: string
  {
    case NotProvisioned = 'notProvisioned';
    case ConflictFound = 'conflictFound';
    case Provisioned = 'provisioned';
    case Drifted = 'drifted';
  }

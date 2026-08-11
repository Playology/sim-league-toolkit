<?php

  namespace SLTK\Provisioning;

  use SLTK\Core\Enums\ProvisioningKind;
  use SLTK\Core\Enums\ProvisioningState;

  class ProvisioningItemStatus {
    public function __construct(
      public readonly string $itemKey,
      public readonly string $label,
      public readonly ProvisioningKind $kind,
      public readonly ProvisioningState $state,
      public readonly ?int $existingId = null,
      public readonly ?string $existingTitle = null,
      public readonly ?string $existingModifiedAt = null
    ) {}

    public function toDto(): array {
      return [
        'itemKey' => $this->itemKey,
        'label' => $this->label,
        'kind' => $this->kind->value,
        'state' => $this->state->value,
        'existingId' => $this->existingId,
        'existingTitle' => $this->existingTitle,
        'existingModifiedAt' => $this->existingModifiedAt,
      ];
    }
  }

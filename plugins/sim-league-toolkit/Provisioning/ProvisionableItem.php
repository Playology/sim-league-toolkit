<?php

  namespace SLTK\Provisioning;

  use SLTK\Core\Enums\ProvisioningKind;

  class ProvisionableItem {
    public function __construct(
      public readonly string $itemKey,
      public readonly string $label,
      public readonly ProvisioningKind $kind,
      public readonly ?string $slug = null,
      public readonly ?\Closure $contentGenerator = null
    ) {}

    public function content(): string {
      return $this->contentGenerator === null ? '' : ($this->contentGenerator)();
    }
  }

<?php

  namespace SLTK\Database\Repositories;

  use Exception;
  use SLTK\Core\Constants;
  use SLTK\Core\Enums\ProvisioningKind;
  use SLTK\Database\TableNames;
  use stdClass;

  class ProvisionedItemsRepository extends RepositoryBase {

    /**
     * @return stdClass[]
     * @throws Exception
     */
    public static function all(): array {
      return self::getResultsFromTable(TableNames::PROVISIONED_ITEMS);
    }

    /**
     * @throws Exception
     */
    public static function deleteByItemKey(string $itemKey): void {
      self::deleteFromTable(TableNames::PROVISIONED_ITEMS, "itemKey = '{$itemKey}'");
    }

    /**
     * @throws Exception
     */
    public static function getByItemKey(string $itemKey): ?stdClass {
      return self::getRowFromTable(TableNames::PROVISIONED_ITEMS, "itemKey = '{$itemKey}'");
    }

    /**
     * Insert-or-update by itemKey: recordProvisioned() is also how a "refresh content" overwrite
     * re-records an already-tracked item, so a blind insert would violate the uniq_item_key
     * constraint on every re-provision after the first.
     *
     * @throws Exception
     */
    public static function recordProvisioned(string $itemKey, int $targetId, ProvisioningKind $kind, string $contentHash, string $provisionedVia): void {
      $data = [
        'itemKey' => $itemKey,
        'targetId' => $targetId,
        'kind' => $kind->value,
        'contentHash' => $contentHash,
        'provisionedVia' => $provisionedVia,
        'provisionedAt' => current_time(Constants::STANDARD_DATE_TIME_FORMAT),
      ];

      $existing = self::getByItemKey($itemKey);

      if ($existing === null) {
        self::insertWithoutReturn(TableNames::PROVISIONED_ITEMS, $data);

        return;
      }

      self::updateById(TableNames::PROVISIONED_ITEMS, (int)$existing->id, $data);
    }
  }

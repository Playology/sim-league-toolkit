import {ProvisioningKind} from './ProvisioningKind';
import {ProvisioningState} from './ProvisioningState';

export interface ProvisioningItemStatus {
    itemKey: string;
    label: string;
    kind: ProvisioningKind;
    state: ProvisioningState;
    existingId: number | null;
    existingTitle: string | null;
    existingModifiedAt: string | null;
}

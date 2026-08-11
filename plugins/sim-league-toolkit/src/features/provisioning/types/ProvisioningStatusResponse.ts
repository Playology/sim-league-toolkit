import {ProvisioningItemStatus} from './ProvisioningItemStatus';

export interface ProvisioningStatusResponse {
    warnings: string[];
    items: ProvisioningItemStatus[];
}

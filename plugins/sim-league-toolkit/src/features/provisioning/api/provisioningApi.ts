import {ApiClient} from '../../../api';

import {ProvisioningAction, ProvisioningItemStatus, ProvisioningStatusResponse} from '../';

const provisioningRoot = '/provisioning';

const endpoints = {
    status: () => `${provisioningRoot}/status`,
    provision: (itemKey: string) => `${provisioningRoot}/${encodeURIComponent(itemKey)}/provision`,
    remove: (itemKey: string) => `${provisioningRoot}/${encodeURIComponent(itemKey)}/remove`,
};

export const provisioningApi = {
    getStatus: async (): Promise<ProvisioningStatusResponse> => {
        const response = await ApiClient.get<ProvisioningStatusResponse>(endpoints.status());
        if (!response.success) {
            throw new Error('Failed to fetch provisioning status');
        }
        return response.data;
    },

    provisionItem: async (itemKey: string, action: ProvisioningAction): Promise<ProvisioningItemStatus> => {
        const response = await ApiClient.post<ProvisioningItemStatus>(endpoints.provision(itemKey), {action});
        if (!response.success) {
            throw new Error('Failed to provision item');
        }
        return response.data;
    },

    removeItem: async (itemKey: string, mode: 'stopTracking' | 'delete', confirmDrift = false): Promise<ProvisioningItemStatus> => {
        const response = await ApiClient.post<ProvisioningItemStatus>(endpoints.remove(itemKey), {mode, confirmDrift});
        if (!response.success) {
            throw new Error('Failed to remove item');
        }
        return response.data;
    },
};

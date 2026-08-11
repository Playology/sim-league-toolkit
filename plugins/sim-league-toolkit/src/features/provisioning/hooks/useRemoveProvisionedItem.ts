import {useMutation, useQueryClient} from '@tanstack/react-query';

import {provisioningQueryKeys} from '../api/provisioningQueryKeys';
import {provisioningApi} from '../api/provisioningApi';

export const useRemoveProvisionedItem = () => {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: ({itemKey, mode, confirmDrift}: { itemKey: string; mode: 'stopTracking' | 'delete'; confirmDrift?: boolean }) =>
            provisioningApi.removeItem(itemKey, mode, confirmDrift),
        onSuccess: async () => {
            await queryClient.invalidateQueries({queryKey: provisioningQueryKeys.status()});
        },
    });
};

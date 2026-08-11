import {useMutation, useQueryClient} from '@tanstack/react-query';

import {provisioningQueryKeys} from '../api/provisioningQueryKeys';
import {provisioningApi} from '../api/provisioningApi';
import {ProvisioningAction} from '../types/ProvisioningAction';

export const useProvisionItem = () => {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: ({itemKey, action}: { itemKey: string; action: ProvisioningAction }) =>
            provisioningApi.provisionItem(itemKey, action),
        onSuccess: async () => {
            await queryClient.invalidateQueries({queryKey: provisioningQueryKeys.status()});
        },
    });
};

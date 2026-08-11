import {useQuery} from '@tanstack/react-query';

import {provisioningQueryKeys} from '../api/provisioningQueryKeys';
import {provisioningApi} from '../api/provisioningApi';

export const useProvisioningStatus = () => {
    return useQuery({
        queryKey: provisioningQueryKeys.status(),
        queryFn: () => provisioningApi.getStatus(),
    });
};

import {useQuery} from '@tanstack/react-query';

import {championshipPlanQueryKeys} from '../api/championshipPlanQueryKeys';
import {championshipPlanApi} from '../api/championshipPlanApi';

export const usePlanTallies = (id: number) => {
    return useQuery({
        queryKey: championshipPlanQueryKeys.tallies(id),
        queryFn: () => championshipPlanApi.getTallies(id),
        enabled: id > 0,
    });
};

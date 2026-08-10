import {useQuery} from '@tanstack/react-query';

import {championshipPlanQueryKeys} from '../api/championshipPlanQueryKeys';
import {championshipPlanApi} from '../api/championshipPlanApi';

export const useAvailablePlanClasses = (planId: number) => {
    return useQuery({
        queryKey: championshipPlanQueryKeys.availableClasses(planId),
        queryFn: () => championshipPlanApi.listAvailableClasses(planId),
        enabled: planId > 0,
    });
};

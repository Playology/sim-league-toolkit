import {useQuery} from '@tanstack/react-query';

import {championshipPlanQueryKeys} from '../api/championshipPlanQueryKeys';
import {championshipPlanApi} from '../api/championshipPlanApi';

export const useAvailablePlanCars = (planId: number) => {
    return useQuery({
        queryKey: championshipPlanQueryKeys.availableCars(planId),
        queryFn: () => championshipPlanApi.listAvailableCars(planId),
        enabled: planId > 0,
    });
};

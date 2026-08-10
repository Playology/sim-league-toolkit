import {useQuery} from '@tanstack/react-query';

import {championshipPlanQueryKeys} from '../api/championshipPlanQueryKeys';
import {championshipPlanApi} from '../api/championshipPlanApi';

export const useChampionshipPlans = () => {
    return useQuery({
        queryKey: championshipPlanQueryKeys.all,
        queryFn: () => championshipPlanApi.list(),
    });
};

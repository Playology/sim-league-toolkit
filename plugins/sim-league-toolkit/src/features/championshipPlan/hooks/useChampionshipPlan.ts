import {useQuery} from '@tanstack/react-query';

import {championshipPlanQueryKeys} from '../api/championshipPlanQueryKeys';
import {championshipPlanApi} from '../api/championshipPlanApi';

export const useChampionshipPlan = (id: number) => {
    return useQuery({
        queryKey: championshipPlanQueryKeys.single(id),
        queryFn: () => championshipPlanApi.getById(id),
        enabled: id > 0,
    });
};

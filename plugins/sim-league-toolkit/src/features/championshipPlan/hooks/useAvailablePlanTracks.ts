import {useQuery} from '@tanstack/react-query';

import {championshipPlanQueryKeys} from '../api/championshipPlanQueryKeys';
import {championshipPlanApi} from '../api/championshipPlanApi';
import {PlanTrackFilter} from '../';

export const useAvailablePlanTracks = (planId: number, filter: PlanTrackFilter) => {
    return useQuery({
        queryKey: championshipPlanQueryKeys.availableTracks(planId, filter),
        queryFn: () => championshipPlanApi.listAvailableTracks(planId, filter),
        enabled: planId > 0,
    });
};

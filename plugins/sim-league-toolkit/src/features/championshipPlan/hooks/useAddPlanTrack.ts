import {useMutation, useQueryClient} from '@tanstack/react-query';

import {championshipPlanQueryKeys} from '../api/championshipPlanQueryKeys';
import {championshipPlanApi} from '../api/championshipPlanApi';

export const useAddPlanTrack = (planId: number) => {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: (trackId: number) => championshipPlanApi.addTrack(planId, trackId),
        onSuccess: async () => {
            await Promise.all([
                queryClient.invalidateQueries({queryKey: championshipPlanQueryKeys.availableTracksAllFilters(planId)}),
                queryClient.invalidateQueries({queryKey: championshipPlanQueryKeys.tallies(planId)}),
            ]);
        },
    });
};

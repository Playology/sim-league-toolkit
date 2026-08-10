import {useMutation, useQueryClient} from '@tanstack/react-query';

import {championshipPlanQueryKeys} from '../api/championshipPlanQueryKeys';
import {championshipPlanApi} from '../api/championshipPlanApi';
import {PlanClassFormData} from '../';

export const useAddPlanClass = (planId: number) => {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: (data: PlanClassFormData) => championshipPlanApi.addClass(planId, data),
        onSuccess: async () => {
            await Promise.all([
                queryClient.invalidateQueries({queryKey: championshipPlanQueryKeys.availableClasses(planId)}),
                queryClient.invalidateQueries({queryKey: championshipPlanQueryKeys.tallies(planId)}),
            ]);
        },
    });
};

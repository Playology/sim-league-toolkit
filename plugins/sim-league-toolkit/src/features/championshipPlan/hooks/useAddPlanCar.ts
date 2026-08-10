import {useMutation, useQueryClient} from '@tanstack/react-query';

import {championshipPlanQueryKeys} from '../api/championshipPlanQueryKeys';
import {championshipPlanApi} from '../api/championshipPlanApi';

export const useAddPlanCar = (planId: number) => {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: (carId: number) => championshipPlanApi.addCar(planId, carId),
        onSuccess: async () => {
            await Promise.all([
                queryClient.invalidateQueries({queryKey: championshipPlanQueryKeys.availableCars(planId)}),
                queryClient.invalidateQueries({queryKey: championshipPlanQueryKeys.tallies(planId)}),
            ]);
        },
    });
};

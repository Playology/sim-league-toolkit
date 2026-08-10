import {useMutation, useQueryClient} from '@tanstack/react-query';

import {championshipPlanQueryKeys} from '../api/championshipPlanQueryKeys';
import {championshipPlanApi} from '../api/championshipPlanApi';

export const useDeleteChampionshipPlan = () => {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: (id: number) => championshipPlanApi.delete(id),
        onSuccess: async () => {
            await queryClient.invalidateQueries({queryKey: championshipPlanQueryKeys.all});
        },
    });
};

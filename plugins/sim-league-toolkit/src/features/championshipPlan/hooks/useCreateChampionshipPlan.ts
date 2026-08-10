import {useMutation, useQueryClient} from '@tanstack/react-query';

import {championshipPlanQueryKeys} from '../api/championshipPlanQueryKeys';
import {championshipPlanApi} from '../api/championshipPlanApi';
import {ChampionshipPlanFormData} from '../';

export const useCreateChampionshipPlan = () => {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: (data: ChampionshipPlanFormData) => championshipPlanApi.create(data),
        onSuccess: async () => {
            await queryClient.invalidateQueries({queryKey: championshipPlanQueryKeys.all});
        },
    });
};

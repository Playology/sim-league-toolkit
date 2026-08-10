import {useMutation, useQueryClient} from '@tanstack/react-query';

import {championshipPlanQueryKeys} from '../api/championshipPlanQueryKeys';
import {championshipPlanApi} from '../api/championshipPlanApi';
import {ChampionshipPlanFormData} from '../';

export const useUpdateChampionshipPlan = () => {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: ({id, data}: { id: number; data: ChampionshipPlanFormData }) => championshipPlanApi.update(id, data),
        onSuccess: async (_, {id}) => {
            await Promise.all([
                queryClient.invalidateQueries({queryKey: championshipPlanQueryKeys.all}),
                queryClient.invalidateQueries({queryKey: championshipPlanQueryKeys.single(id)}),
            ]);
        },
    });
};

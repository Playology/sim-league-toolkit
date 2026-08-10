import {useMutation, useQueryClient} from '@tanstack/react-query';

import {championshipPlanQueryKeys} from '../api/championshipPlanQueryKeys';
import {championshipPlanApi} from '../api/championshipPlanApi';

export const useSetPlanCreatedChampionship = () => {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: ({id, championshipId}: { id: number; championshipId: number }) => championshipPlanApi.setCreatedChampionship(id, championshipId),
        onSuccess: async (_, {id}) => {
            await Promise.all([
                queryClient.invalidateQueries({queryKey: championshipPlanQueryKeys.all}),
                queryClient.invalidateQueries({queryKey: championshipPlanQueryKeys.single(id)}),
            ]);
        },
    });
};

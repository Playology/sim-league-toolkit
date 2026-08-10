import {useMutation, useQueryClient} from '@tanstack/react-query';

import {championshipQueryKeys} from '../../championship/api/championshipQueryKeys';
import {championshipBuilderApi} from '../api/championshipBuilderApi';
import {ChampionshipBuilderFormData} from '../types/ChampionshipBuilderFormData';

export const useBuildChampionship = () => {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: (data: ChampionshipBuilderFormData) => championshipBuilderApi.build(data),
        onSuccess: async () => {
            await queryClient.invalidateQueries({queryKey: championshipQueryKeys.all});
        },
    });
};

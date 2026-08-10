import {ApiClient} from '../../../api';

import {ChampionshipBuilderFormData} from '../types/ChampionshipBuilderFormData';

const endpoints = {
    build: '/championship-builder',
};

export const championshipBuilderApi = {
    build: async (data: ChampionshipBuilderFormData): Promise<number> => {
        const response = await ApiClient.post<{id: number}>(endpoints.build, data);
        if (!response.success) {
            throw new Error('Failed to build championship');
        }
        return response.data.id;
    },
};

import {useQuery} from '@tanstack/react-query';

import {gameApi} from '../api/gameApi';
import {gameQueryKeys} from '../api/gameQueryKeys';

export const useLastChampionshipTrackIds = (gameId: number) => {
    return useQuery({
                        queryKey: gameQueryKeys.lastChampionshipTrackIds(gameId),
                        queryFn: () => gameApi.listLastChampionshipTrackIds(gameId),
                        enabled: gameId > 0,
                    });
};

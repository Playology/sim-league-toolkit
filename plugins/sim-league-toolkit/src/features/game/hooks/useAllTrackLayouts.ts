import {useQuery} from '@tanstack/react-query';

import {gameApi} from '../api/gameApi';
import {gameQueryKeys} from '../api/gameQueryKeys';

export const useAllTrackLayouts = (gameId: number) => {
    return useQuery({
                        queryKey: gameQueryKeys.allTrackLayouts(gameId),
                        queryFn: () => gameApi.listAllTrackLayouts(gameId),
                        enabled: gameId > 0,
                    });
};

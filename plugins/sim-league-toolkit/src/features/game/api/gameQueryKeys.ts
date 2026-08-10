export const gameQueryKeys = {
    all: ['games'] as const,
    single: (id: number) => ['games', id] as const,
    config: (gameKay: string) => ['games', gameKay, 'config'] as const,
    carClasses: (gameId: number) => ['games', gameId, 'cars', 'classes'] as const,
    cars: (gameId: number) => ['games', gameId, 'cars'] as const,
    carsByClass: (gameId: number, carClass: string) => ['games', gameId, 'cars', carClass] as const,
    platforms: (gameId: number) => ['games', gameId, 'platforms'] as const,
    tracks: (gameId: number) => ['games', gameId, 'tracks'] as const,
    trackLayouts: (trackId: number) => ['games', 'tracks', trackId, 'layouts'] as const,
    allTrackLayouts: (gameId: number) => ['games', gameId, 'track-layouts'] as const,
    lastChampionshipTrackIds: (gameId: number) => ['games', gameId, 'last-championship-track-ids'] as const,
} as const;
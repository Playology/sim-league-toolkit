import {ApiClient} from '../../../api';
import {Game, GameConfig, Car, Track, TrackLayout, Platform} from '../';

const gameRootEndpoint = '/game';
const gamesRootEndpoint = '/games';
const gameConfigRootEndpoint =  '/game-config';

const endpoints = {
    get: gamesRootEndpoint,
    getById: (gameId: number)=> `${gameRootEndpoint}/${gameId}`,
    getConfig: (gameKey: string) => `${gameConfigRootEndpoint}/${gameKey}`,
    getCarClasses: (gameId: number) => `${gameRootEndpoint}/${gameId}/car-classes`,
    getCars: (gameId: number) => `${gameRootEndpoint}/${gameId}/cars`,
    getCarsByClass: (gameId: number, carClass: string) => `${gameRootEndpoint}/${gameId}/cars/${carClass}`,
    getPlatforms: (gameId: number) => `${gameRootEndpoint}/${gameId}/platforms`,
    getTracks: (gameId: number) => `${gameRootEndpoint}/${gameId}/tracks`,
    getTrackLayouts: (trackId: number) => `${gameRootEndpoint}/tracks/${trackId}`,
    getAllTrackLayouts: (gameId: number) => `${gameRootEndpoint}/${gameId}/track-layouts`,
    getLastChampionshipTrackIds: (gameId: number) => `${gameRootEndpoint}/${gameId}/last-championship-track-ids`,
}

export const gameApi = {
    list: async (): Promise<Game[]> => {
        const response = await ApiClient.get<Game[]>(endpoints.get);
        if (!response.success) {
            throw new Error('Failed to fetch games');
        }
        return response.data ?? [];
    },

    getById: async (id: number): Promise<Game> => {
        const response = await ApiClient.get<Game>(endpoints.getById(id));
        if (!response.success) {
            throw new Error(`Failed to fetch game with ${id}`);
        }
        return response.data;
    },

    getConfig: async (gameKey: string): Promise<GameConfig> => {
        const response = await ApiClient.get<GameConfig>(endpoints.getConfig(gameKey));
        if (!response.success) {
            throw new Error(`Failed to fetch game config for ${gameKey}`);
        }
        return response.data;
    },

    listCarClasses: async (gameId: number): Promise<string[]> => {
        const response = await ApiClient.get<string[]>(endpoints.getCarClasses(gameId));
        if (!response.success) {
            throw new Error(`Failed to fetch car classes for ${gameId}`);
        }
        return response.data;

    },

    listCars: async (gameId: number): Promise<Car[]> => {
        const response = await ApiClient.get<Car[]>(endpoints.getCars(gameId));
        if (!response.success) {
            throw new Error(`Failed to fetch cars for ${gameId}`);
        }
        return response.data;

    },

    listCarsByClass: async (gameId: number, carClass: string): Promise<Car[]> => {
        const response = await ApiClient.get<Car[]>(endpoints.getCarsByClass(gameId, carClass));
        if (!response.success) {
            throw new Error(`Failed to fetch cars for ${gameId} and ${carClass}`);
        }
        return response.data;

    },

    listPlatforms: async (gameId: number): Promise<Platform[]> => {
        const response = await ApiClient.get<Platform[]>(endpoints.getPlatforms(gameId));
        if (!response.success) {
            throw new Error(`Failed to fetch platforms for ${gameId}`);
        }
        return response.data;

    },

    listTracks: async (gameId: number): Promise<Track[]> => {
        const response = await ApiClient.get<Track[]>(endpoints.getTracks(gameId));
        if (!response.success) {
            throw new Error(`Failed to fetch tracks for ${gameId}`);
        }
        return response.data;

    },

    listTrackLayouts: async (trackId: number): Promise<TrackLayout[]> => {
        const response = await ApiClient.get<TrackLayout[]>(endpoints.getTrackLayouts(trackId));
        if (!response.success) {
            throw new Error(`Failed to fetch layouts for ${trackId}`);
        }
        return response.data;

    },

    listAllTrackLayouts: async (gameId: number): Promise<TrackLayout[]> => {
        const response = await ApiClient.get<TrackLayout[]>(endpoints.getAllTrackLayouts(gameId));
        if (!response.success) {
            throw new Error(`Failed to fetch track layouts for ${gameId}`);
        }
        return response.data;

    },

    listLastChampionshipTrackIds: async (gameId: number): Promise<number[]> => {
        const response = await ApiClient.get<number[]>(endpoints.getLastChampionshipTrackIds(gameId));
        if (!response.success) {
            throw new Error(`Failed to fetch last championship track ids for ${gameId}`);
        }
        return response.data ?? [];

    }
}
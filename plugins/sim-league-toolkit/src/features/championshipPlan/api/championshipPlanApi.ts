import {ApiClient} from '../../../api';

import {AvailablePlanCar, AvailablePlanTrack, ChampionshipPlan, ChampionshipPlanFormData, ChampionshipPlanTallies, PlanClassFormData, PlanTrackFilter} from '../';
import {EventClass} from '../../eventClass';

const planRoot = '/championship-plan';
const plansRoot = '/championship-plans';

const buildTrackFilterQuery = (filter: PlanTrackFilter): string => {
    const params = new URLSearchParams();
    if (filter.excludeLastChampionshipTracks) {
        params.set('excludeLastChampionshipTracks', '1');
    }
    if (filter.excludeDlc) {
        params.set('excludeDlc', '1');
    }
    if (filter.minLength !== undefined) {
        params.set('minLength', String(filter.minLength));
    }
    if (filter.maxLength !== undefined) {
        params.set('maxLength', String(filter.maxLength));
    }
    const query = params.toString();
    return query.length > 0 ? `?${query}` : '';
};

const endpoints = {
    list: plansRoot,
    getById: (id: number) => `${planRoot}/${id}`,
    create: planRoot,
    update: (id: number) => `${planRoot}/${id}`,
    delete: (id: number) => `${planRoot}/${id}`,
    setCreatedChampionship: (id: number) => `${planRoot}/${id}/created-championship`,
    tallies: (id: number) => `${planRoot}/${id}/tallies`,
    availableTracks: (id: number, filter: PlanTrackFilter) => `${planRoot}/${id}/tracks/available${buildTrackFilterQuery(filter)}`,
    addTrack: (id: number) => `${planRoot}/${id}/tracks`,
    addTracksBulk: (id: number) => `${planRoot}/${id}/tracks/bulk`,
    removeTrack: (id: number, trackId: number) => `${planRoot}/${id}/tracks/${trackId}`,
    availableCars: (id: number) => `${planRoot}/${id}/cars/available`,
    addCar: (id: number) => `${planRoot}/${id}/cars`,
    removeCar: (id: number, carId: number) => `${planRoot}/${id}/cars/${carId}`,
    availableClasses: (id: number) => `${planRoot}/${id}/classes/available`,
    addClass: (id: number) => `${planRoot}/${id}/classes`,
    removeClass: (id: number, planClassId: number) => `${planRoot}/${id}/classes/${planClassId}`,
};

export const championshipPlanApi = {
    list: async (): Promise<ChampionshipPlan[]> => {
        const response = await ApiClient.get<ChampionshipPlan[]>(endpoints.list);
        if (!response.success) {
            throw new Error('Failed to fetch championship plans');
        }
        return response.data ?? [];
    },

    getById: async (id: number): Promise<ChampionshipPlan> => {
        const response = await ApiClient.get<ChampionshipPlan>(endpoints.getById(id));
        if (!response.success) {
            throw new Error(`Failed to fetch championship plan with id ${id}`);
        }
        return response.data;
    },

    create: async (data: ChampionshipPlanFormData): Promise<number> => {
        const response = await ApiClient.post<number>(endpoints.create, data);
        if (!response.success) {
            throw new Error('Failed to create championship plan');
        }
        return response.data;
    },

    update: async (id: number, data: ChampionshipPlanFormData): Promise<void> => {
        const response = await ApiClient.put<void>(endpoints.update(id), data);
        if (!response.success) {
            throw new Error(`Failed to update championship plan with id ${id}`);
        }
    },

    delete: async (id: number): Promise<void> => {
        const response = await ApiClient.delete(endpoints.delete(id));
        if (!response.success) {
            throw new Error(`Failed to delete championship plan with id ${id}`);
        }
    },

    setCreatedChampionship: async (id: number, championshipId: number): Promise<void> => {
        const response = await ApiClient.post<void>(endpoints.setCreatedChampionship(id), {championshipId});
        if (!response.success) {
            throw new Error(`Failed to link championship plan ${id} to championship ${championshipId}`);
        }
    },

    getTallies: async (id: number): Promise<ChampionshipPlanTallies> => {
        const response = await ApiClient.get<ChampionshipPlanTallies>(endpoints.tallies(id));
        if (!response.success) {
            throw new Error(`Failed to fetch tallies for championship plan with id ${id}`);
        }
        return response.data;
    },

    listAvailableTracks: async (id: number, filter: PlanTrackFilter): Promise<AvailablePlanTrack[]> => {
        const response = await ApiClient.get<AvailablePlanTrack[]>(endpoints.availableTracks(id, filter));
        if (!response.success) {
            throw new Error(`Failed to fetch available tracks for championship plan with id ${id}`);
        }
        return response.data ?? [];
    },

    addTrack: async (id: number, trackId: number): Promise<void> => {
        const response = await ApiClient.post<void>(endpoints.addTrack(id), {trackId});
        if (!response.success) {
            throw new Error(`Failed to add track to championship plan with id ${id}`);
        }
    },

    addTracksBulk: async (id: number, trackIds: number[]): Promise<void> => {
        const response = await ApiClient.post<void>(endpoints.addTracksBulk(id), {trackIds});
        if (!response.success) {
            throw new Error(`Failed to bulk-add tracks to championship plan with id ${id}`);
        }
    },

    removeTrack: async (id: number, trackId: number): Promise<void> => {
        const response = await ApiClient.delete(endpoints.removeTrack(id, trackId));
        if (!response.success) {
            throw new Error(`Failed to remove track from championship plan with id ${id}`);
        }
    },

    listAvailableCars: async (id: number): Promise<AvailablePlanCar[]> => {
        const response = await ApiClient.get<AvailablePlanCar[]>(endpoints.availableCars(id));
        if (!response.success) {
            throw new Error(`Failed to fetch available cars for championship plan with id ${id}`);
        }
        return response.data ?? [];
    },

    addCar: async (id: number, carId: number): Promise<void> => {
        const response = await ApiClient.post<void>(endpoints.addCar(id), {carId});
        if (!response.success) {
            throw new Error(`Failed to add car to championship plan with id ${id}`);
        }
    },

    removeCar: async (id: number, carId: number): Promise<void> => {
        const response = await ApiClient.delete(endpoints.removeCar(id, carId));
        if (!response.success) {
            throw new Error(`Failed to remove car from championship plan with id ${id}`);
        }
    },

    listAvailableClasses: async (id: number): Promise<EventClass[]> => {
        const response = await ApiClient.get<EventClass[]>(endpoints.availableClasses(id));
        if (!response.success) {
            throw new Error(`Failed to fetch available classes for championship plan with id ${id}`);
        }
        return response.data ?? [];
    },

    addClass: async (id: number, data: PlanClassFormData): Promise<number> => {
        const response = await ApiClient.post<number>(endpoints.addClass(id), data);
        if (!response.success) {
            throw new Error(`Failed to add class to championship plan with id ${id}`);
        }
        return response.data;
    },

    removeClass: async (id: number, planClassId: number): Promise<void> => {
        const response = await ApiClient.delete(endpoints.removeClass(id, planClassId));
        if (!response.success) {
            throw new Error(`Failed to remove class from championship plan with id ${id}`);
        }
    },
};

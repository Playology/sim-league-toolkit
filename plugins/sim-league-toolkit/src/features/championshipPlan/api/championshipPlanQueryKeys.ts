import {PlanTrackFilter} from '../';

const championshipPlan = 'championship-plan';

export const championshipPlanQueryKeys = {
    all: [championshipPlan] as const,
    single: (id: number) => [championshipPlan, id] as const,
    tallies: (id: number) => [championshipPlan, id, 'tallies'] as const,
    ballot: (id: number) => [championshipPlan, id, 'ballot'] as const,
    availableTracks: (id: number, filter: PlanTrackFilter) => [championshipPlan, id, 'tracks', 'available', filter] as const,
    availableTracksAllFilters: (id: number) => [championshipPlan, id, 'tracks', 'available'] as const,
    availableCars: (id: number) => [championshipPlan, id, 'cars', 'available'] as const,
    availableClasses: (id: number) => [championshipPlan, id, 'classes', 'available'] as const,
};

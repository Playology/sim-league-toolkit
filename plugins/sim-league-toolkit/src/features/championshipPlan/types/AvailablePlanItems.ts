export interface AvailablePlanTrack {
    trackId: number;
    trackName: string;
}

export interface PlanTrackFilter {
    excludeLastChampionshipTracks: boolean;
    excludeDlc: boolean;
    minLength?: number;
    maxLength?: number;
}

export interface AvailablePlanCar {
    carId: number;
    carName: string;
    carClass: string;
}

export interface PlanClassFormData {
    eventClassId: number;
    name: string;
    carClass: string;
}

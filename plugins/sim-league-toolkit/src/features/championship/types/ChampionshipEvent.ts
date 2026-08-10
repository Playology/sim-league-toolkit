export interface ChampionshipEvent {
    id: number;
    eventRefId: number;
    championshipId: number;
    trackId: number;
    trackLayoutId?: number;
    trackMasterCarId?: number;
    name: string;
    startDateTime: string;
    isActive: boolean;
    isCompleted: boolean;
    bannerImageUrl: string;
    championship?: string;
    track?: string;
    trackLayout?: string;
    trackMasterCar?: string;
    trophiesAwarded: boolean;
}

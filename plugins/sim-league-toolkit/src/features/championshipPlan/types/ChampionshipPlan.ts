import {ChampionshipType} from '../../../enums/generated/ChampionshipType';
import {PlanStatus} from '../../../enums/generated/PlanStatus';

export interface ChampionshipPlan {
    id: number;
    name: string;
    description: string;
    game?: string;
    gameId: number;
    platform?: string;
    platformId: number;
    championshipType: ChampionshipType;
    status: PlanStatus;
    startDate: Date;
    classesFixed: boolean;
    maxTrackVotesPerUser: number;
    maxCarVotesPerUser: number;
    maxCarsPerClass: number;
    createdChampionshipId?: number;
}

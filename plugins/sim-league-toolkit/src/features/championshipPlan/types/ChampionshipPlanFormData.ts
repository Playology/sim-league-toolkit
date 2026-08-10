import {ChampionshipType} from '../../../enums/generated/ChampionshipType';
import {PlanStatus} from '../../../enums/generated/PlanStatus';

export interface ChampionshipPlanFormData {
    name: string;
    description: string;
    gameId: number;
    platformId: number;
    championshipType: ChampionshipType;
    status: PlanStatus;
    startDate: Date;
    classesFixed: boolean;
    maxTrackVotesPerUser: number;
    maxCarVotesPerUser: number;
    maxCarsPerClass: number;
}

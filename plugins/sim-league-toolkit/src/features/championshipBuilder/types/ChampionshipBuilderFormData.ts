import {ChampionshipType} from '../../../enums/generated/ChampionshipType';
import {SessionType} from '../../../enums/generated/SessionType';

export interface ChampionshipBuilderClassFormData {
    eventClassId?: number;
    name?: string;
    carClass?: string;
    isSingleCarClass?: boolean;
    singleCarId?: number;
    driverCategoryId?: number;
}

export interface ChampionshipBuilderRoundFormData {
    trackId?: number;
    trackLayoutId?: number;
    carId?: number;
}

export interface ChampionshipBuilderSessionTemplateFormData {
    sessionType: SessionType;
    count: number;
    attributes: Record<string, unknown>;
}

export interface ChampionshipBuilderFormData {
    name: string;
    description: string;
    gameId: number;
    platformId: number;
    championshipType: ChampionshipType;
    startDate: Date;
    ruleSetId: number;
    scoringSetId: number;
    resultsToDiscard: number;
    entryChangeLimit: number;
    maxEntrants: number;
    eventStartTime: string;
    eventStartIntervalDays: number;
    trackMasterTrackId?: number;
    trackMasterTrackLayoutId?: number;
    classes: ChampionshipBuilderClassFormData[];
    rounds: ChampionshipBuilderRoundFormData[];
    sessionTemplates: ChampionshipBuilderSessionTemplateFormData[];
}

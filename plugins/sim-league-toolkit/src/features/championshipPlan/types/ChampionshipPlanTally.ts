export interface ChampionshipPlanTrackTally {
    trackId: number;
    trackName: string;
    voteCount: number;
    favouriteCount: number;
    currentUserVoted: boolean;
    currentUserFavourited: boolean;
}

export interface ChampionshipPlanCarTally {
    carId: number;
    carName: string;
    carClass: string;
    voteCount: number;
    currentUserVoted: boolean;
}

export interface ChampionshipPlanClassTally {
    planClassId: number;
    eventClassId?: number;
    name: string;
    carClass: string;
    driverCategoryId?: number;
    driverCategoryName?: string;
    isSingleCarClass: boolean;
    singleCarId?: number;
    singleCarName?: string;
    suggestedByUserId?: number;
    suggestedByName: string;
    voteCount: number;
    currentUserVoted: boolean;
}

export interface ChampionshipPlanTallies {
    tracks: ChampionshipPlanTrackTally[];
    cars: ChampionshipPlanCarTally[];
    classes: ChampionshipPlanClassTally[];
}

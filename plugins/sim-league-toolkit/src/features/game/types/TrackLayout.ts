import {Entity} from "../../../types/Entity";

export interface TrackLayout extends Entity {
    corners: number;
    dlcPack?: string;
    game?: string;
    gameId: number;
    layoutId: string;
    length: number;
    name: string;
    track?: string;
    trackId: number;
}
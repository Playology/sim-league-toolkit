import {__} from '@wordpress/i18n';
import {useState} from '@wordpress/element';

import {Button} from 'primereact/button';
import {Checkbox} from 'primereact/checkbox';
import {DataTable} from 'primereact/datatable';
import {Column} from 'primereact/column';
import {RadioButton} from 'primereact/radiobutton';

import {ChampionshipPlan, ChampionshipPlanCarTally, ChampionshipPlanClassTally, ChampionshipPlanTrackTally, usePlanTallies, useSetPlanCreatedChampionship} from '../../../features/championshipPlan';
import {ChampionshipBuilderFormData} from '../../../features/championshipBuilder';
import {ChampionshipBuilderWizard} from '../championshipBuilder/ChampionshipBuilderWizard';
import {ChampionshipType} from '../../../enums/generated/ChampionshipType';
import {PlanStatus} from '../../../enums/generated/PlanStatus';

interface PlanResultsTabProps {
    plan: ChampionshipPlan;
}

export const PlanResultsTab = ({plan}: PlanResultsTabProps) => {
    const {data: tallies} = usePlanTallies(plan.id);
    const {mutateAsync: setCreatedChampionship} = useSetPlanCreatedChampionship();

    const [selectedTrackIds, setSelectedTrackIds] = useState<number[]>([]);
    const [trackMasterTrackId, setTrackMasterTrackId] = useState<number | null>(null);
    const [selectedCarIds, setSelectedCarIds] = useState<number[]>([]);
    const [selectedPlanClassIds, setSelectedPlanClassIds] = useState<number[]>([]);
    const [isBuilding, setIsBuilding] = useState(false);

    const isTrackMaster = plan.championshipType === ChampionshipType.TRACK_MASTER;
    const canBuild = plan.status === PlanStatus.CLOSED && !plan.createdChampionshipId;

    const toggleTrack = (trackId: number, checked: boolean) => {
        setSelectedTrackIds(prev => checked ? [...prev, trackId] : prev.filter(id => id !== trackId));
    };

    const toggleCar = (carId: number, checked: boolean) => {
        setSelectedCarIds(prev => checked ? [...prev, carId] : prev.filter(id => id !== carId));
    };

    const toggleClass = (planClassId: number, checked: boolean) => {
        setSelectedPlanClassIds(prev => checked ? [...prev, planClassId] : prev.filter(id => id !== planClassId));
    };

    const buildInitialFormData = (): Partial<ChampionshipBuilderFormData> => {
        const tracks = tallies?.tracks ?? [];
        const cars = tallies?.cars ?? [];
        const classes = (tallies?.classes ?? []).filter(c => selectedPlanClassIds.includes(c.planClassId));

        const formData: Partial<ChampionshipBuilderFormData> = {
            name: plan.name,
            description: plan.description,
            gameId: plan.gameId,
            platformId: plan.platformId,
            championshipType: plan.championshipType,
            startDate: new Date(plan.startDate),
            classes: classes.map(c => ({
                eventClassId: c.eventClassId,
                name: c.name,
                carClass: c.carClass,
                isSingleCarClass: c.isSingleCarClass,
                singleCarId: c.singleCarId,
                driverCategoryId: c.driverCategoryId,
            })),
        };

        if (isTrackMaster) {
            const track = tracks.find(t => t.trackId === trackMasterTrackId);
            formData.trackMasterTrackId = track?.trackId;
            formData.rounds = cars.filter(c => selectedCarIds.includes(c.carId)).map(c => ({carId: c.carId}));
        } else {
            formData.rounds = tracks.filter(t => selectedTrackIds.includes(t.trackId))
                .map(t => ({trackId: t.trackId}));
        }

        return formData;
    };

    const onBuild = () => setIsBuilding(true);

    const onBuilderSaved = async (championshipId: number) => {
        await setCreatedChampionship({id: plan.id, championshipId});
        setIsBuilding(false);
    };

    if (isBuilding) {
        return (
            <ChampionshipBuilderWizard initialFormData={buildInitialFormData()}
                                       onSaved={onBuilderSaved}
                                       onCancelled={() => setIsBuilding(false)}/>
        );
    }

    if (plan.createdChampionshipId) {
        return (
            <p>
                {__('This plan has already been converted into Championship #', 'sim-league-toolkit')}{plan.createdChampionshipId}.
            </p>
        );
    }

    return (
        <>
            <p>
                {isTrackMaster
                    ? __('Pick the winning track for the season and the cars that will become each round, then create the Championship.', 'sim-league-toolkit')
                    : __('Pick the winning tracks - each becomes one round - then create the Championship.', 'sim-league-toolkit')}
            </p>

            <h4>{__('Tracks', 'sim-league-toolkit')}</h4>
            <DataTable value={tallies?.tracks ?? []} dataKey='trackId'>
                {isTrackMaster ? (
                    <Column header={__('Fixed Track', 'sim-league-toolkit')} body={(t: ChampionshipPlanTrackTally) => (
                        <RadioButton checked={trackMasterTrackId === t.trackId}
                                     onChange={() => setTrackMasterTrackId(t.trackId)}/>
                    )}/>
                ) : (
                    <Column header={__('Round', 'sim-league-toolkit')} body={(t: ChampionshipPlanTrackTally) => (
                        <Checkbox checked={selectedTrackIds.includes(t.trackId)}
                                  onChange={(e) => toggleTrack(t.trackId, e.checked)}/>
                    )}/>
                )}
                <Column field='trackName' header={__('Track', 'sim-league-toolkit')}/>
                <Column field='voteCount' header={__('Votes', 'sim-league-toolkit')}/>
                <Column field='favouriteCount' header={__('Favourites', 'sim-league-toolkit')}/>
            </DataTable>

            {isTrackMaster && <>
                <h4>{__('Cars', 'sim-league-toolkit')}</h4>
                <DataTable value={tallies?.cars ?? []} dataKey='carId'>
                    <Column header={__('Round', 'sim-league-toolkit')} body={(c: ChampionshipPlanCarTally) => (
                        <Checkbox checked={selectedCarIds.includes(c.carId)}
                                  onChange={(e) => toggleCar(c.carId, e.checked)}/>
                    )}/>
                    <Column field='carName' header={__('Car', 'sim-league-toolkit')}/>
                    <Column field='carClass' header={__('Class', 'sim-league-toolkit')}/>
                    <Column field='voteCount' header={__('Votes', 'sim-league-toolkit')}/>
                </DataTable>
            </>}

            <h4>{__('Classes', 'sim-league-toolkit')}</h4>
            <DataTable value={tallies?.classes ?? []} dataKey='planClassId'>
                <Column header={__('Include', 'sim-league-toolkit')} body={(c: ChampionshipPlanClassTally) => (
                    <Checkbox checked={selectedPlanClassIds.includes(c.planClassId)}
                              onChange={(e) => toggleClass(c.planClassId, e.checked)}/>
                )}/>
                <Column field='name' header={__('Class', 'sim-league-toolkit')}/>
                <Column field='suggestedByName' header={__('Suggested By', 'sim-league-toolkit')}/>
                <Column field='voteCount' header={__('Votes', 'sim-league-toolkit')}/>
            </DataTable>

            {plan.status !== PlanStatus.CLOSED && (
                <p><em>{__('Close the plan to stop voting and enable creating the Championship.', 'sim-league-toolkit')}</em></p>
            )}

            <Button onClick={onBuild} disabled={!canBuild} className='mt-3'>
                {__('Create Championship', 'sim-league-toolkit')}
            </Button>
        </>
    );
};

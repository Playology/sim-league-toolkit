import {__} from '@wordpress/i18n';
import {useState} from '@wordpress/element';

import {Button} from 'primereact/button';
import {Checkbox} from 'primereact/checkbox';
import {DataView} from 'primereact/dataview';
import {Dropdown, DropdownChangeEvent} from 'primereact/dropdown';
import {InputNumber} from 'primereact/inputnumber';

import {ChampionshipPlanTrackTally, PlanTrackFilter, useAddPlanTrack, useAddPlanTracksBulk, useAvailablePlanTracks, usePlanTallies, useRemovePlanTrack} from '../../../features/championshipPlan';

interface PlanTracksTabProps {
    planId: number;
}

const defaultFilter: PlanTrackFilter = {
    excludeLastChampionshipTracks: false,
    excludeDlc: false,
};

export const PlanTracksTab = ({planId}: PlanTracksTabProps) => {
    const {data: tallies, isLoading: talliesLoading} = usePlanTallies(planId);
    const [filter, setFilter] = useState<PlanTrackFilter>(defaultFilter);
    const {data: availableTracks = [], isLoading: availableLoading} = useAvailablePlanTracks(planId, filter);
    const {mutateAsync: addTrack, isPending: isAdding} = useAddPlanTrack(planId);
    const {mutateAsync: addTracksBulk, isPending: isAddingAll} = useAddPlanTracksBulk(planId);
    const {mutateAsync: removeTrack} = useRemovePlanTrack(planId);

    const [selectedTrackId, setSelectedTrackId] = useState(0);

    const onSelect = (e: DropdownChangeEvent) => {
        setSelectedTrackId(e.target.value);
    };

    const onAdd = async () => {
        if (selectedTrackId === 0) {
            return;
        }

        await addTrack(selectedTrackId);
        setSelectedTrackId(0);
    };

    const onAddAll = async () => {
        if (availableTracks.length === 0) {
            return;
        }

        await addTracksBulk(availableTracks.map(t => t.trackId));
    };

    const onRemove = async (tally: ChampionshipPlanTrackTally) => {
        await removeTrack(tally.trackId);
    };

    const listItems = availableTracks.map(t => ({
        value: t.trackId,
        label: t.trackName,
    }));

    const itemTemplate = (tally: ChampionshipPlanTrackTally) => {
        return (
            <div className='flex flex-row justify-content-between align-items-center gap-2'
                 style={{border: '1px solid #ddd', borderRadius: '4px', padding: '.5rem 1rem', margin: '.25rem'}}
                 key={tally.trackId}>
                <span>{tally.trackName}</span>
                <span>{__('Votes', 'sim-league-toolkit')}: {tally.voteCount} | {__('Favourites', 'sim-league-toolkit')}: {tally.favouriteCount}</span>
                <Button icon='pi pi-times' severity='danger' size='small' onClick={() => onRemove(tally)}/>
            </div>
        );
    };

    return (
        <>
            <p>
                {__('Add candidate tracks for members to vote on. The most-voted tracks can be carried through to the Championship Builder once the plan is closed (the specific layout, if the game has one, is picked in the Builder).', 'sim-league-toolkit')}
            </p>

            <div className='flex flex-row flex-wrap align-items-center gap-4' style={{marginBottom: '.75rem'}}>
                <div className='flex align-items-center gap-2'>
                    <Checkbox inputId='exclude-last-championship' checked={filter.excludeLastChampionshipTracks}
                              onChange={(e) => setFilter(prev => ({...prev, excludeLastChampionshipTracks: e.checked}))}/>
                    <label htmlFor='exclude-last-championship'>{__('Exclude tracks from the last championship', 'sim-league-toolkit')}</label>
                </div>
                <div className='flex align-items-center gap-2'>
                    <Checkbox inputId='exclude-dlc' checked={filter.excludeDlc}
                              onChange={(e) => setFilter(prev => ({...prev, excludeDlc: e.checked}))}/>
                    <label htmlFor='exclude-dlc'>{__('Exclude DLC tracks', 'sim-league-toolkit')}</label>
                </div>
                <div className='flex align-items-center gap-2'>
                    <label htmlFor='min-length'>{__('Min Length (m)', 'sim-league-toolkit')}</label>
                    <InputNumber inputId='min-length' value={filter.minLength ?? null}
                                 onValueChange={(e) => setFilter(prev => ({...prev, minLength: e.value ?? undefined}))}
                                 min={0} inputStyle={{width: '6rem'}}/>
                    <label htmlFor='max-length'>{__('Max Length (m)', 'sim-league-toolkit')}</label>
                    <InputNumber inputId='max-length' value={filter.maxLength ?? null}
                                 onValueChange={(e) => setFilter(prev => ({...prev, maxLength: e.value ?? undefined}))}
                                 min={0} inputStyle={{width: '6rem'}}/>
                </div>
            </div>

            <div className='flex flex-row align-items-center gap-2'>
                <Dropdown value={selectedTrackId} options={listItems} onChange={onSelect}
                          optionLabel='label' optionValue='value' disabled={availableLoading}
                          placeholder={__('Select a track...', 'sim-league-toolkit')}/>
                <Button onClick={onAdd} disabled={isAdding || selectedTrackId === 0}
                        icon='pi pi-plus' size='small'/>
                <Button label={__('Add All', 'sim-league-toolkit')} onClick={onAddAll}
                        disabled={isAddingAll || availableLoading || availableTracks.length === 0}
                        icon='pi pi-plus-circle' size='small' severity='secondary'/>
            </div>
            <DataView value={tallies?.tracks ?? []} itemTemplate={itemTemplate} layout='list'
                      loading={talliesLoading}
                      header={__('Track Pool', 'sim-league-toolkit')}
                      emptyMessage={__('No tracks have been added yet.', 'sim-league-toolkit')}/>
        </>
    );
};

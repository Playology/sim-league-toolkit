import {__} from '@wordpress/i18n';
import {useState} from '@wordpress/element';

import {Button} from 'primereact/button';
import {DataView} from 'primereact/dataview';
import {Dropdown, DropdownChangeEvent} from 'primereact/dropdown';

import {ChampionshipPlanClassTally, useAddPlanClass, useAvailablePlanClasses, usePlanTallies, useRemovePlanClass} from '../../../features/championshipPlan';

interface PlanClassesTabProps {
    planId: number;
    classesFixed: boolean;
}

export const PlanClassesTab = ({planId, classesFixed}: PlanClassesTabProps) => {
    const {data: tallies, isLoading: talliesLoading} = usePlanTallies(planId);
    const {data: availableClasses = [], isLoading: availableLoading} = useAvailablePlanClasses(planId);
    const {mutateAsync: addClass, isPending: isAdding} = useAddPlanClass(planId);
    const {mutateAsync: removeClass} = useRemovePlanClass(planId);

    const [selectedEventClassId, setSelectedEventClassId] = useState(0);

    const onSelect = (e: DropdownChangeEvent) => {
        setSelectedEventClassId(e.target.value);
    };

    const onAdd = async () => {
        const selected = availableClasses.find(c => c.id === selectedEventClassId);
        if (!selected) {
            return;
        }

        await addClass({eventClassId: selected.id, name: selected.name, carClass: selected.carClass});
        setSelectedEventClassId(0);
    };

    const onRemove = async (tally: ChampionshipPlanClassTally) => {
        await removeClass(tally.planClassId);
    };

    const listItems = availableClasses.map(c => ({
        value: c.id,
        label: c.name,
    }));

    const itemTemplate = (tally: ChampionshipPlanClassTally) => {
        return (
            <div className='flex flex-row justify-content-between align-items-center gap-2'
                 style={{border: '1px solid #ddd', borderRadius: '4px', padding: '.5rem 1rem', margin: '.25rem'}}
                 key={tally.planClassId}>
                <span>{tally.name}</span>
                <span>{__('Suggested by', 'sim-league-toolkit')}: {tally.suggestedByName}</span>
                {!classesFixed && <span>{__('Votes', 'sim-league-toolkit')}: {tally.voteCount}</span>}
                <Button icon='pi pi-times' severity='danger' size='small' onClick={() => onRemove(tally)}/>
            </div>
        );
    };

    return (
        <>
            <p>
                {classesFixed
                    ? __('Add the classes members will be able to enter once the plan becomes a Championship. Classes are fixed by the admin for this plan, not voted on.', 'sim-league-toolkit')
                    : __('Add starting candidate classes. Members can also suggest and vote on classes while the plan is open.', 'sim-league-toolkit')}
            </p>
            <div className='flex flex-row align-items-center gap-2'>
                <Dropdown value={selectedEventClassId} options={listItems} onChange={onSelect}
                          optionLabel='label' optionValue='value' disabled={availableLoading}
                          placeholder={__('Select a class...', 'sim-league-toolkit')}/>
                <Button onClick={onAdd} disabled={isAdding || selectedEventClassId === 0}
                        icon='pi pi-plus' size='small'/>
            </div>
            <DataView value={tallies?.classes ?? []} itemTemplate={itemTemplate} layout='list'
                      loading={talliesLoading}
                      header={__('Class Pool', 'sim-league-toolkit')}
                      emptyMessage={__('No classes have been added yet.', 'sim-league-toolkit')}/>
        </>
    );
};

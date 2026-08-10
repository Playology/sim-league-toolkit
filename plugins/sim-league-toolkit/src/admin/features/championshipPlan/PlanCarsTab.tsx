import {__} from '@wordpress/i18n';
import {useState} from '@wordpress/element';

import {Button} from 'primereact/button';
import {DataView} from 'primereact/dataview';
import {Dropdown, DropdownChangeEvent} from 'primereact/dropdown';

import {ChampionshipPlanCarTally, useAddPlanCar, useAvailablePlanCars, usePlanTallies, useRemovePlanCar} from '../../../features/championshipPlan';

interface PlanCarsTabProps {
    planId: number;
}

export const PlanCarsTab = ({planId}: PlanCarsTabProps) => {
    const {data: tallies, isLoading: talliesLoading} = usePlanTallies(planId);
    const {data: availableCars = [], isLoading: availableLoading} = useAvailablePlanCars(planId);
    const {mutateAsync: addCar, isPending: isAdding} = useAddPlanCar(planId);
    const {mutateAsync: removeCar} = useRemovePlanCar(planId);

    const [selectedCarId, setSelectedCarId] = useState(0);

    const onSelect = (e: DropdownChangeEvent) => {
        setSelectedCarId(e.target.value);
    };

    const onAdd = async () => {
        if (selectedCarId === 0) {
            return;
        }

        await addCar(selectedCarId);
        setSelectedCarId(0);
    };

    const onRemove = async (tally: ChampionshipPlanCarTally) => {
        await removeCar(tally.carId);
    };

    const listItems = availableCars.map(c => ({
        value: c.carId,
        label: `${c.carName} (${c.carClass})`,
    }));

    const itemTemplate = (tally: ChampionshipPlanCarTally) => {
        return (
            <div className='flex flex-row justify-content-between align-items-center gap-2'
                 style={{border: '1px solid #ddd', borderRadius: '4px', padding: '.5rem 1rem', margin: '.25rem'}}
                 key={tally.carId}>
                <span>{tally.carName} ({tally.carClass})</span>
                <span>{__('Votes', 'sim-league-toolkit')}: {tally.voteCount}</span>
                <Button icon='pi pi-times' severity='danger' size='small' onClick={() => onRemove(tally)}/>
            </div>
        );
    };

    return (
        <>
            <p>
                {__('Add candidate cars, one "car of the week" per round, for members to vote on. Track Master rounds are built from the most-voted cars once the plan is closed.', 'sim-league-toolkit')}
            </p>
            <div className='flex flex-row align-items-center gap-2'>
                <Dropdown value={selectedCarId} options={listItems} onChange={onSelect}
                          optionLabel='label' optionValue='value' disabled={availableLoading}
                          placeholder={__('Select a car...', 'sim-league-toolkit')}/>
                <Button onClick={onAdd} disabled={isAdding || selectedCarId === 0}
                        icon='pi pi-plus' size='small'/>
            </div>
            <DataView value={tallies?.cars ?? []} itemTemplate={itemTemplate} layout='list'
                      loading={talliesLoading}
                      header={__('Car Pool', 'sim-league-toolkit')}
                      emptyMessage={__('No cars have been added yet.', 'sim-league-toolkit')}/>
        </>
    );
};

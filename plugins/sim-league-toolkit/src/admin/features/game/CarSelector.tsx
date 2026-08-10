import {__} from '@wordpress/i18n';
import {useEffect, useState} from '@wordpress/element';

import {Dropdown, DropdownChangeEvent} from 'primereact/dropdown';

import {Car, useCars} from '../../../features/game';
import {ListItem} from '../../types/ListItem';
import {ValidationError} from '../../components/ValidationError';

interface CarSelectorProps {
    gameId: number;
    onSelectedItemChanged: (item: Car) => void;
    carId?: number;
    carClass?: string;
    disabled?: boolean;
    id?: string;
    isInvalid?: boolean;
    validationMessage?: string;
}

export const CarSelector = ({
                                gameId,
                                onSelectedItemChanged,
                                carId = 0,
                                carClass = '*',
                                disabled = false,
                                id = 'car-selector',
                                isInvalid = false,
                                validationMessage = ''
                            }: CarSelectorProps) => {

    const {data = [], isLoading} = useCars(gameId, carClass !== '*' ? carClass : undefined);

    const [selectedItemId, setSelectedItemId] = useState(carId);

    useEffect(() => {
        setSelectedItemId(carId);
    }, [carId]);

    const onSelect = (e: DropdownChangeEvent) => {
        setSelectedItemId(e.target.value);

        const selectedCar = data.find(car => car.id === e.target.value);
        if (selectedCar) {
            onSelectedItemChanged(selectedCar);
        }
    };

    const listItems: ListItem[] = ([{
        value: 0,
        label: __('Please select...', 'sim-league-toolkit')
    }] as ListItem[]).concat(data.map(i => ({
        value: i.id,
        label: `${i.name} (${i.year})`
    })));

    return (
        <>
            <label htmlFor={id}>{__('Car', 'sim-league-toolkit')}</label>
            <Dropdown id={id} value={selectedItemId} options={listItems} onChange={onSelect}
                      optionLabel='label'
                      optionValue='value' disabled={disabled || isLoading}/>
            <ValidationError
                message={validationMessage}
                show={isInvalid}/>
        </>
    );
};
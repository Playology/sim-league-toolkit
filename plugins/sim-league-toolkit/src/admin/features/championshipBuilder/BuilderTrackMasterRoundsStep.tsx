import {__} from '@wordpress/i18n';
import {useState} from '@wordpress/element';

import {Button} from 'primereact/button';
import {InputNumber} from 'primereact/inputnumber';

import {ChampionshipBuilderFormData} from '../../../features/championshipBuilder';
import {CarSelector} from '../game/CarSelector';
import {ValidationError} from '../../components/ValidationError';

interface BuilderTrackMasterRoundsStepProps {
    formData: ChampionshipBuilderFormData;
    onChange: (updates: Partial<ChampionshipBuilderFormData>) => void;
    onNext: () => void;
    onBack: () => void;
}

export const BuilderTrackMasterRoundsStep = ({formData, onChange, onNext, onBack}: BuilderTrackMasterRoundsStepProps) => {
    const [validationErrors, setValidationErrors] = useState<string[]>([]);

    const roundCount = formData.rounds.length || 2;

    const setRoundCount = (count: number) => {
        const clamped = Math.max(2, count);
        const rounds = [...formData.rounds];

        if (clamped > rounds.length) {
            while (rounds.length < clamped) {
                rounds.push({});
            }
        } else if (clamped < rounds.length) {
            rounds.length = clamped;
        }

        onChange({rounds});
    };

    const updateRoundCar = (index: number, carId: number) => {
        const rounds = [...formData.rounds];
        rounds[index] = {carId};
        onChange({rounds});
    };

    const validate = () => {
        const errors: string[] = [];

        if (formData.rounds.length < 2) {
            errors.push('roundCount');
        }

        if (formData.rounds.some(r => !r.carId)) {
            errors.push('roundCars');
        }

        setValidationErrors(errors);
        return errors.length === 0;
    };

    const onClickNext = () => {
        if (!validate()) {
            return;
        }
        onNext();
    };

    return (
        <>
            <h4>{__('Number of Rounds', 'sim-league-toolkit')}</h4>
            <div className='flex align-items-center gap-2'>
                <InputNumber value={roundCount} onValueChange={(e) => setRoundCount(e.value ?? 2)} min={2}/>
            </div>
            <ValidationError message={__('You must configure at least two Rounds to form a Championship.',
                                          'sim-league-toolkit')}
                             show={validationErrors.includes('roundCount')}/>

            <h4>{__('Round Cars', 'sim-league-toolkit')}</h4>
            <p>{__('A different car is used for each round.', 'sim-league-toolkit')}</p>
            <div className='flex flex-column align-items-stretch gap-2' style={{maxWidth: '350px'}}>
                {formData.rounds.map((round, index) => (
                    <CarSelector key={index} id={`builder-round-car-${index}`} gameId={formData.gameId}
                                 carId={round.carId}
                                 onSelectedItemChanged={(car) => updateRoundCar(index, car.id)}
                                 isInvalid={validationErrors.includes('roundCars') && !round.carId}
                                 validationMessage={__('You must select a car for every round.',
                                                       'sim-league-toolkit')}/>
                ))}
            </div>

            <div className='mt-4'>
                <Button label={__('Back', 'sim-league-toolkit')} severity='secondary' onClick={onBack}/>
                <Button label={__('Next', 'sim-league-toolkit')} onClick={onClickNext}
                        style={{marginLeft: '.5rem'}}/>
            </div>
        </>
    );
};

import {__} from '@wordpress/i18n';
import {useState} from '@wordpress/element';
import {FormEvent} from 'react';

import {Calendar} from 'primereact/calendar';
import {Checkbox} from 'primereact/checkbox';
import {InputNumber} from 'primereact/inputnumber';
import {InputText} from 'primereact/inputtext';
import {InputTextarea} from 'primereact/inputtextarea';

import {BusyIndicator} from '../../components/BusyIndicator';
import {CancelButton} from '../../components/CancelButton';
import {ChampionshipPlanFormData, useCreateChampionshipPlan} from '../../../features/championshipPlan';
import {ChampionshipType} from '../../../enums/generated/ChampionshipType';
import {ChampionshipTypeSelector} from '../championship/ChampionshipTypeSelector';
import {GameSelector} from '../game/GameSelector';
import {PlanStatus} from '../../../enums/generated/PlanStatus';
import {PlatformSelector} from '../game/PlatformSelector';
import {SaveSubmitButton} from '../../components/SaveSubmitButton';
import {ValidationError} from '../../components/ValidationError';

interface NewChampionshipPlanEditorProps {
    onSaved: () => void;
    onCancelled: () => void;
}

const minDate = new Date();

export const NewChampionshipPlanEditor = ({onSaved, onCancelled}: NewChampionshipPlanEditorProps) => {
    const {mutateAsync: createPlan, isPending: isLoading} = useCreateChampionshipPlan();

    const [championshipType, setChampionshipType] = useState<ChampionshipType>(ChampionshipType.STANDARD);
    const [classesFixed, setClassesFixed] = useState(true);
    const [description, setDescription] = useState('');
    const [gameId, setGameId] = useState(0);
    const [maxCarVotesPerUser, setMaxCarVotesPerUser] = useState(1);
    const [maxCarsPerClass, setMaxCarsPerClass] = useState(1);
    const [maxTrackVotesPerUser, setMaxTrackVotesPerUser] = useState(1);
    const [name, setName] = useState('');
    const [platformId, setPlatformId] = useState(0);
    const [startDate, setStartDate] = useState(minDate);
    const [validationErrors, setValidationErrors] = useState<string[]>([]);

    const isTrackMaster = championshipType === ChampionshipType.TRACK_MASTER;

    const onSave = async (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        if (!validate()) {
            return;
        }

        const formData: ChampionshipPlanFormData = {
            name,
            description,
            gameId,
            platformId,
            championshipType,
            status: PlanStatus.DRAFT,
            startDate,
            classesFixed,
            maxTrackVotesPerUser,
            maxCarVotesPerUser,
            maxCarsPerClass,
        };

        await createPlan(formData);
        onSaved();
    };

    const validate = () => {
        const errors = [];

        if (gameId < 1) {
            errors.push('game');
        }

        if (platformId < 1) {
            errors.push('platform');
        }

        if (!name || name.length < 5) {
            errors.push('name');
        }

        if (!description || description.length < 15) {
            errors.push('description');
        }

        setValidationErrors(errors);
        return errors.length === 0;
    };

    return (
        <>
            <BusyIndicator isBusy={isLoading}/>
            <h3>{__('New Championship Plan', 'sim-league-toolkit')}</h3>
            <form onSubmit={onSave} noValidate>
                <GameSelector gameId={gameId}
                              isInvalid={validationErrors.includes('game')}
                              validationMessage={__('You must select the game that this plan will use.', 'sim-league-toolkit')}
                              onSelectedItemChanged={setGameId}/>
                {gameId !== 0 &&
                    <div className='flex flex-row flex-wrap justify-content-start gap-4'>
                        <div className='flex flex-column align-items-stretch gap-2' style={{minWidth: '350px'}}>
                            <PlatformSelector gameId={gameId}
                                              isInvalid={validationErrors.includes('platform')}
                                              validationMessage={__('You must select the platform the plan will use.', 'sim-league-toolkit')}
                                              onSelectedItemChanged={setPlatformId}
                                              platformId={platformId}/>
                            <label htmlFor='plan-name'>{__('Name', 'sim-league-toolkit')}</label>
                            <InputText id='plan-name' value={name}
                                       onChange={(e) => setName(e.target.value)}
                                       placeholder={__('Enter Name', 'sim-league-toolkit')}/>
                            <ValidationError
                                message={__('A name with at least 5 characters is required', 'sim-league-toolkit')}
                                show={validationErrors.includes('name')}/>
                            <label htmlFor='plan-description'>{__('Description', 'sim-league-toolkit')}</label>
                            <InputTextarea id='plan-description' value={description}
                                           onChange={(e) => setDescription(e.target.value)}
                                           placeholder={__('Enter a Description', 'sim-league-toolkit')}
                                           rows={5} cols={40}/>
                            <ValidationError
                                message={__('A brief description with at least 15 characters is required.', 'sim-league-toolkit')}
                                show={validationErrors.includes('description')}/>
                            <label htmlFor='plan-start-date'>{__('Target Start Date', 'sim-league-toolkit')}</label>
                            <Calendar id='plan-start-date' value={startDate} onChange={(e) => setStartDate(e.value)}
                                      minDate={minDate} readOnlyInput dateFormat='D, M d yy'/>
                            <ChampionshipTypeSelector championshipType={championshipType}
                                                      onSelectedItemChanged={setChampionshipType}
                                                      disabled={isLoading}/>
                        </div>
                        <div className='flex flex-column align-items-stretch gap-2' style={{minWidth: '350px'}}>
                            <div className='flex flex-row justify-content-between'>
                                <label htmlFor='classes-fixed'>{__('Admin curates classes (no member voting)', 'sim-league-toolkit')}</label>
                                <Checkbox id='classes-fixed' checked={classesFixed}
                                          onChange={(e) => setClassesFixed(e.checked)}
                                          style={{marginTop: '.75rem'}}/>
                            </div>
                            <label htmlFor='max-track-votes'>{__('Max Track Votes Per Member', 'sim-league-toolkit')}</label>
                            <InputNumber id='max-track-votes' value={maxTrackVotesPerUser}
                                         onChange={(e) => setMaxTrackVotesPerUser(e.value ?? 1)} min={1}/>
                            {isTrackMaster && <>
                                <label htmlFor='max-car-votes'>{__('Max Car Votes Per Member', 'sim-league-toolkit')}</label>
                                <InputNumber id='max-car-votes' value={maxCarVotesPerUser}
                                             onChange={(e) => setMaxCarVotesPerUser(e.value ?? 1)} min={1}/>
                                <label htmlFor='max-cars-per-class'>{__('Max Cars Per Class Per Member', 'sim-league-toolkit')}</label>
                                <InputNumber id='max-cars-per-class' value={maxCarsPerClass}
                                             onChange={(e) => setMaxCarsPerClass(e.value ?? 1)} min={1}/>
                            </>}
                        </div>
                    </div>
                }
                <SaveSubmitButton disabled={isLoading} name='submitForm'/>
                <CancelButton onCancel={onCancelled} disabled={isLoading}/>
            </form>
        </>
    );
};

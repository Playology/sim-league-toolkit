import {__} from '@wordpress/i18n';
import {useEffect, useState} from '@wordpress/element';
import {FormEvent} from 'react';

import {Accordion, AccordionTab} from 'primereact/accordion';
import {Calendar} from 'primereact/calendar';
import {Checkbox} from 'primereact/checkbox';
import {InputNumber} from 'primereact/inputnumber';
import {InputText} from 'primereact/inputtext';
import {InputTextarea} from 'primereact/inputtextarea';

import {BusyIndicator} from '../../components/BusyIndicator';
import {CancelButton} from '../../components/CancelButton';
import {ChampionshipPlan, ChampionshipPlanFormData, useUpdateChampionshipPlan} from '../../../features/championshipPlan';
import {ChampionshipType, ChampionshipTypeLabels} from '../../../enums/generated/ChampionshipType';
import {PlanCarsTab} from './PlanCarsTab';
import {PlanClassesTab} from './PlanClassesTab';
import {PlanResultsTab} from './PlanResultsTab';
import {PlanStatus, PlanStatusOptions} from '../../../enums/generated/PlanStatus';
import {PlanTracksTab} from './PlanTracksTab';
import {PlatformSelector} from '../game/PlatformSelector';
import {SaveSubmitButton} from '../../components/SaveSubmitButton';
import {ValidationError} from '../../components/ValidationError';
import {Dropdown, DropdownChangeEvent} from 'primereact/dropdown';

interface ChampionshipPlanEditorProps {
    plan: ChampionshipPlan;
    onSaved: () => void;
    onCancelled: () => void;
}

const minDate = new Date();

export const ChampionshipPlanEditor = ({plan, onSaved, onCancelled}: ChampionshipPlanEditorProps) => {
    const {mutateAsync: updatePlan, isPending: isLoading} = useUpdateChampionshipPlan();

    const [activeTabIndex, setActiveTabIndex] = useState<number | number[]>(0);
    const [championshipType, setChampionshipType] = useState<ChampionshipType>(plan.championshipType);
    const [classesFixed, setClassesFixed] = useState(plan.classesFixed);
    const [description, setDescription] = useState(plan.description);
    const [maxCarVotesPerUser, setMaxCarVotesPerUser] = useState(plan.maxCarVotesPerUser);
    const [maxCarsPerClass, setMaxCarsPerClass] = useState(plan.maxCarsPerClass);
    const [maxTrackVotesPerUser, setMaxTrackVotesPerUser] = useState(plan.maxTrackVotesPerUser);
    const [name, setName] = useState(plan.name);
    const [platformId, setPlatformId] = useState(plan.platformId);
    const [startDate, setStartDate] = useState(new Date(plan.startDate));
    const [status, setStatus] = useState<PlanStatus>(plan.status);
    const [validationErrors, setValidationErrors] = useState<string[]>([]);

    useEffect(() => {
        setChampionshipType(plan.championshipType);
        setClassesFixed(plan.classesFixed);
        setDescription(plan.description);
        setMaxCarVotesPerUser(plan.maxCarVotesPerUser);
        setMaxCarsPerClass(plan.maxCarsPerClass);
        setMaxTrackVotesPerUser(plan.maxTrackVotesPerUser);
        setName(plan.name);
        setPlatformId(plan.platformId);
        setStartDate(new Date(plan.startDate));
        setStatus(plan.status);
    }, [plan]);

    const isTrackMaster = championshipType === ChampionshipType.TRACK_MASTER;
    const isLocked = !!plan.createdChampionshipId;

    const onSave = async (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        if (!validate()) {
            return;
        }

        const formData: ChampionshipPlanFormData = {
            name,
            description,
            gameId: plan.gameId,
            platformId,
            championshipType,
            status,
            startDate,
            classesFixed,
            maxTrackVotesPerUser,
            maxCarVotesPerUser,
            maxCarsPerClass,
        };

        await updatePlan({id: plan.id, data: formData});

        onSaved();
    };

    const validate = () => {
        const errors = [];

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
            <h3>{__('Championship Plan', 'sim-league-toolkit')} - {name}</h3>
            <h4>{__('Game', 'sim-league-toolkit')} - {plan.game}</h4>
            <h4>{__('Type', 'sim-league-toolkit')} - {ChampionshipTypeLabels[championshipType]}</h4>
            <Accordion activeIndex={activeTabIndex} onTabChange={(e) => setActiveTabIndex(e.index)}>
                <AccordionTab header={__('Details', 'sim-league-toolkit')}>
                    <form onSubmit={onSave} noValidate>
                        <div className='flex flex-row flex-wrap justify-content-start gap-4'>
                            <div className='flex flex-column align-items-stretch gap-2' style={{minWidth: '350px'}}>
                                <PlatformSelector gameId={plan.gameId}
                                                  isInvalid={validationErrors.includes('platform')}
                                                  validationMessage={__('You must select the platform the plan will use.', 'sim-league-toolkit')}
                                                  onSelectedItemChanged={setPlatformId}
                                                  platformId={platformId}
                                                  disabled={isLocked}/>
                                <label htmlFor='plan-name'>{__('Name', 'sim-league-toolkit')}</label>
                                <InputText id='plan-name' value={name}
                                           onChange={(e) => setName(e.target.value)} disabled={isLocked}/>
                                <ValidationError
                                    message={__('A name with at least 5 characters is required', 'sim-league-toolkit')}
                                    show={validationErrors.includes('name')}/>
                                <label htmlFor='plan-description'>{__('Description', 'sim-league-toolkit')}</label>
                                <InputTextarea id='plan-description' value={description}
                                               onChange={(e) => setDescription(e.target.value)}
                                               rows={5} cols={40} disabled={isLocked}/>
                                <ValidationError
                                    message={__('A brief description with at least 15 characters is required.', 'sim-league-toolkit')}
                                    show={validationErrors.includes('description')}/>
                                <label htmlFor='plan-start-date'>{__('Target Start Date', 'sim-league-toolkit')}</label>
                                <Calendar id='plan-start-date' value={startDate} onChange={(e) => setStartDate(e.value)}
                                          minDate={minDate} readOnlyInput dateFormat='D, M d yy' disabled={isLocked}/>
                                <label htmlFor='plan-status'>{__('Status', 'sim-league-toolkit')}</label>
                                <Dropdown id='plan-status' value={status} options={[...PlanStatusOptions]}
                                          optionLabel='label' optionValue='value'
                                          onChange={(e: DropdownChangeEvent) => setStatus(e.value)}
                                          disabled={isLocked}/>
                            </div>
                            <div className='flex flex-column align-items-stretch gap-2' style={{minWidth: '350px'}}>
                                <div className='flex flex-row justify-content-between'>
                                    <label htmlFor='classes-fixed'>{__('Admin curates classes (no member voting)', 'sim-league-toolkit')}</label>
                                    <Checkbox id='classes-fixed' checked={classesFixed}
                                              onChange={(e) => setClassesFixed(e.checked)}
                                              disabled={isLocked}
                                              style={{marginTop: '.75rem'}}/>
                                </div>
                                <label htmlFor='max-track-votes'>{__('Max Track Votes Per Member', 'sim-league-toolkit')}</label>
                                <InputNumber id='max-track-votes' value={maxTrackVotesPerUser}
                                             onChange={(e) => setMaxTrackVotesPerUser(e.value ?? 1)} min={1}
                                             disabled={isLocked}/>
                                {isTrackMaster && <>
                                    <label htmlFor='max-car-votes'>{__('Max Car Votes Per Member', 'sim-league-toolkit')}</label>
                                    <InputNumber id='max-car-votes' value={maxCarVotesPerUser}
                                                 onChange={(e) => setMaxCarVotesPerUser(e.value ?? 1)} min={1}
                                                 disabled={isLocked}/>
                                    <label htmlFor='max-cars-per-class'>{__('Max Cars Per Class Per Member', 'sim-league-toolkit')}</label>
                                    <InputNumber id='max-cars-per-class' value={maxCarsPerClass}
                                                 onChange={(e) => setMaxCarsPerClass(e.value ?? 1)} min={1}
                                                 disabled={isLocked}/>
                                </>}
                            </div>
                        </div>
                        <SaveSubmitButton disabled={isLoading || isLocked} name='submitForm'/>
                        <CancelButton onCancel={onCancelled} disabled={isLoading}/>
                    </form>
                </AccordionTab>
                <AccordionTab header={__('Tracks', 'sim-league-toolkit')}>
                    <PlanTracksTab planId={plan.id}/>
                </AccordionTab>
                {isTrackMaster && (
                    <AccordionTab header={__('Cars', 'sim-league-toolkit')}>
                        <PlanCarsTab planId={plan.id}/>
                    </AccordionTab>
                )}
                <AccordionTab header={__('Classes', 'sim-league-toolkit')}>
                    <PlanClassesTab planId={plan.id} classesFixed={classesFixed}/>
                </AccordionTab>
                <AccordionTab header={__('Results', 'sim-league-toolkit')}>
                    <PlanResultsTab plan={plan}/>
                </AccordionTab>
            </Accordion>
        </>
    );
};

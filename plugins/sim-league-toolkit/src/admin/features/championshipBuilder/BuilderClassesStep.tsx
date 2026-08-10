import {__} from '@wordpress/i18n';
import {useState} from '@wordpress/element';

import {Button} from 'primereact/button';
import {Checkbox} from 'primereact/checkbox';
import {InputText} from 'primereact/inputtext';

import {ChampionshipBuilderClassFormData, ChampionshipBuilderFormData} from '../../../features/championshipBuilder';
import {ChampionshipType} from '../../../enums/generated/ChampionshipType';
import {CAR_CLASS_SELECTOR_DEFAULT_VALUE, CarClassSelector} from '../game/CarClassSelector';
import {CarSelector} from '../game/CarSelector';
import {DriverCategorySelector} from '../eventClass/DriverCategorySelector';
import {useEventClassesByGame} from '../../../features/eventClass';
import {ValidationError} from '../../components/ValidationError';

interface BuilderClassesStepProps {
    formData: ChampionshipBuilderFormData;
    onChange: (updates: Partial<ChampionshipBuilderFormData>) => void;
    onNext: () => void;
    onBack: () => void;
}

export const BuilderClassesStep = ({formData, onChange, onNext, onBack}: BuilderClassesStepProps) => {
    const {data: availableClasses = []} = useEventClassesByGame(formData.gameId);

    const isTrackMaster = formData.championshipType === ChampionshipType.TRACK_MASTER;

    const [newClassName, setNewClassName] = useState('');
    const [newClassCarClass, setNewClassCarClass] = useState(CAR_CLASS_SELECTOR_DEFAULT_VALUE);
    const [newClassIsSingleCarClass, setNewClassIsSingleCarClass] = useState(false);
    const [newClassSingleCarId, setNewClassSingleCarId] = useState(0);
    const [newClassDriverCategoryId, setNewClassDriverCategoryId] = useState(0);
    const [addValidationErrors, setAddValidationErrors] = useState<string[]>([]);
    const [validationErrors, setValidationErrors] = useState<string[]>([]);

    const selectedExistingIds = formData.classes.filter(c => c.eventClassId).map(c => c.eventClassId);
    const newClasses = formData.classes.filter(c => !c.eventClassId);

    const resetAddForm = () => {
        setNewClassName('');
        setNewClassCarClass(CAR_CLASS_SELECTOR_DEFAULT_VALUE);
        setNewClassIsSingleCarClass(false);
        setNewClassSingleCarId(0);
        setNewClassDriverCategoryId(0);
        setAddValidationErrors([]);
    };

    const toggleExistingClass = (eventClassId: number, checked: boolean) => {
        if (checked) {
            onChange({classes: [...formData.classes, {eventClassId}]});
        } else {
            onChange({classes: formData.classes.filter(c => c.eventClassId !== eventClassId)});
        }
    };

    const removeNewClass = (index: number) => {
        const updated = [...newClasses];
        updated.splice(index, 1);
        onChange({classes: [...formData.classes.filter(c => c.eventClassId), ...updated]});
    };

    const validateAddForm = () => {
        const errors: string[] = [];

        if (!newClassName || newClassName.length < 5) {
            errors.push('name');
        }

        if (newClassDriverCategoryId < 1) {
            errors.push('driverCategoryId');
        }

        if (!isTrackMaster) {
            if (newClassCarClass === CAR_CLASS_SELECTOR_DEFAULT_VALUE) {
                errors.push('carClass');
            }

            if (newClassIsSingleCarClass && newClassSingleCarId < 1) {
                errors.push('singleCarId');
            }
        }

        setAddValidationErrors(errors);
        return errors.length === 0;
    };

    const onAddClass = () => {
        if (!validateAddForm()) {
            return;
        }

        const newClass: ChampionshipBuilderClassFormData = {
            name: newClassName,
            driverCategoryId: newClassDriverCategoryId,
            carClass: isTrackMaster ? undefined : newClassCarClass,
            isSingleCarClass: isTrackMaster ? false : newClassIsSingleCarClass,
            singleCarId: (!isTrackMaster && newClassIsSingleCarClass) ? newClassSingleCarId : undefined,
        };

        onChange({classes: [...formData.classes, newClass]});
        resetAddForm();
    };

    const validate = () => {
        const errors: string[] = [];

        if (formData.classes.length === 0) {
            errors.push('classes');
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
            <p>{__('Select the Classes the Championship will include.', 'sim-league-toolkit')}</p>
            <fieldset>
                <legend>{__('Existing Classes', 'sim-league-toolkit')}</legend>
                {availableClasses.length === 0 &&
                    <p>{__('There are no existing Classes for this game yet — add one below.',
                           'sim-league-toolkit')}</p>}
                <ul className='list-none p-0'>
                    {availableClasses.map(eventClass => (
                        <li key={eventClass.id} className='flex align-items-center gap-2 mb-1'>
                            <Checkbox inputId={`builder-class-${eventClass.id}`}
                                      checked={selectedExistingIds.includes(eventClass.id)}
                                      onChange={(e) => toggleExistingClass(eventClass.id, e.checked)}/>
                            <label htmlFor={`builder-class-${eventClass.id}`}>{eventClass.name}</label>
                        </li>
                    ))}
                </ul>
            </fieldset>
            <ValidationError message={__('You must select or add at least one Class.', 'sim-league-toolkit')}
                             show={validationErrors.includes('classes')}/>

            {newClasses.length > 0 && <>
                <h4>{__('New Classes', 'sim-league-toolkit')}</h4>
                <ul className='list-none p-0'>
                    {newClasses.map((c, index) => (
                        <li key={index} className='flex align-items-center gap-2 mb-1'>
                            <span>{c.name}</span>
                            <Button icon='pi pi-times' severity='secondary' text
                                    onClick={() => removeNewClass(index)}
                                    aria-label={__('Remove', 'sim-league-toolkit')}/>
                        </li>
                    ))}
                </ul>
            </>}

            <h4>{__('Add a Custom Class', 'sim-league-toolkit')}</h4>
            <div className='flex flex-column align-items-stretch gap-2' style={{maxWidth: '350px'}}>
                <label htmlFor='builder-new-class-name'>{__('Name', 'sim-league-toolkit')}</label>
                <InputText id='builder-new-class-name' value={newClassName}
                           onChange={(e) => setNewClassName(e.target.value)}
                           placeholder={__('Enter Name', 'sim-league-toolkit')}/>
                <ValidationError
                    message={__('A name with at least 5 characters is required', 'sim-league-toolkit')}
                    show={addValidationErrors.includes('name')}/>

                <DriverCategorySelector driverCategoryId={newClassDriverCategoryId}
                                        isInvalid={addValidationErrors.includes('driverCategoryId')}
                                        onSelectedItemChanged={setNewClassDriverCategoryId}
                                        validationMessage={__('You must select a driver category.',
                                                              'sim-league-toolkit')}/>

                {isTrackMaster
                    ? <p>{__(
                        "Car class is fixed to 'Any' for Track Master championships — the car for each round is chosen in a later step.",
                        'sim-league-toolkit')}</p>
                    : <>
                        <CarClassSelector gameId={formData.gameId} carClass={newClassCarClass}
                                          isInvalid={addValidationErrors.includes('carClass')}
                                          onSelectedItemChanged={setNewClassCarClass}
                                          validationMessage={__('You must select a car class.',
                                                                'sim-league-toolkit')}/>
                        <div className='flex flex-row justify-content-between'>
                            <label htmlFor='builder-new-class-single-car'>{__('Is Fixed Car',
                                                                               'sim-league-toolkit')}</label>
                            <Checkbox inputId='builder-new-class-single-car' checked={newClassIsSingleCarClass}
                                      onChange={(e) => setNewClassIsSingleCarClass(e.checked)}/>
                        </div>
                        {newClassIsSingleCarClass && newClassCarClass !== CAR_CLASS_SELECTOR_DEFAULT_VALUE &&
                            <CarSelector gameId={formData.gameId} carClass={newClassCarClass}
                                         carId={newClassSingleCarId}
                                         isInvalid={addValidationErrors.includes('singleCarId')}
                                         onSelectedItemChanged={(c) => setNewClassSingleCarId(c.id)}
                                         validationMessage={__(
                                             'When Is Fixed Car is enabled you must select a car.',
                                             'sim-league-toolkit')}/>}
                    </>}
            </div>
            <div className='mt-2'>
                <Button label={__('Add Class', 'sim-league-toolkit')} severity='secondary' onClick={onAddClass}/>
            </div>

            <div className='mt-4'>
                <Button label={__('Back', 'sim-league-toolkit')} severity='secondary' onClick={onBack}/>
                <Button label={__('Next', 'sim-league-toolkit')} onClick={onClickNext}
                        style={{marginLeft: '.5rem'}}/>
            </div>
        </>
    );
};

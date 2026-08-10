import {__} from '@wordpress/i18n';
import {useState, useEffect} from '@wordpress/element';

import {Calendar} from 'primereact/calendar';
import {Dialog} from 'primereact/dialog';
import {FormEvent} from 'react';
import {InputText} from 'primereact/inputtext';

import {BusyIndicator} from '../../components/BusyIndicator';
import {CancelButton} from '../../components/CancelButton';
import {CarSelector} from '../game/CarSelector';
import {ChampionshipEventFormData, useCreateChampionshipEvent} from '../../../features/championship';
import {ChampionshipType} from '../../../enums/generated/ChampionshipType';
import {SaveSubmitButton} from '../../components/SaveSubmitButton';
import {TrackSelector} from '../game/TrackSelector';
import {useGame} from '../../../features/game';
import {ValidationError} from '../../components/ValidationError';

interface NewChampionshipEventEditorProps {
    championshipId: number;
    gameId: number;
    championshipType: ChampionshipType;
    trackMasterTrackId?: number;
    trackMasterTrackLayoutId?: number;
    onSaved: () => void;
    onCancelled: () => void;
}

const minDate = new Date();
minDate.setHours(0);
minDate.setMinutes(0);
minDate.setSeconds(0);
minDate.setMilliseconds(0);

const defaultTime = new Date();
defaultTime.setHours(14);
defaultTime.setMinutes(0);
defaultTime.setSeconds(0);
defaultTime.setMilliseconds(0);

export const NewChampionshipEventEditor = ({
                                               championshipId,
                                               gameId,
                                               championshipType,
                                               trackMasterTrackId,
                                               trackMasterTrackLayoutId,
                                               onSaved,
                                               onCancelled
                                           }: NewChampionshipEventEditorProps) => {
    const {mutateAsync: createChampionshipEvent, isPending: isLoading} = useCreateChampionshipEvent(championshipId);
    const {data: game, isLoading: gameIsLoading} = useGame(gameId);

    const isTrackMaster = championshipType === ChampionshipType.TRACK_MASTER;

    const [gameSupportsLayouts, setGameSupportsLayouts] = useState(false);
    const [name, setName] = useState('');
    const [startDateTime, setStartDateTime] = useState(minDate);
    const [trackId, setTrackId] = useState(isTrackMaster ? trackMasterTrackId ?? 0 : 0);
    const [trackLayoutId, setTrackLayoutId] = useState(isTrackMaster ? trackMasterTrackLayoutId ?? 0 : 0);
    const [trackMasterCarId, setTrackMasterCarId] = useState(0);
    const [validationErrors, setValidationErrors] = useState<string[]>([]);

    useEffect(() => {
        if (game) {
            setGameSupportsLayouts(game.supportsLayouts);
        }
    }, [game]);

    const onSave = async (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        if (!validate()) {
            return;
        }

        const formData: ChampionshipEventFormData = {
            isActive: false,
            name,
            startDateTime: startDateTime.toISOString(),
            trackId
        };

        if (gameSupportsLayouts) {
            formData.trackLayoutId = trackLayoutId;
        }

        if (isTrackMaster) {
            formData.trackMasterCarId = trackMasterCarId;
        }

        await createChampionshipEvent(formData);
        onSaved();
        resetForm();
    };

    const resetForm = () => {
        setName('');
        setStartDateTime(minDate);
        setTrackId(isTrackMaster ? trackMasterTrackId ?? 0 : 0);
        setTrackLayoutId(isTrackMaster ? trackMasterTrackLayoutId ?? 0 : 0);
        setTrackMasterCarId(0);
        setValidationErrors([]);
    };

    const validate = () => {
        const errors = [];

        if (!name || name.length < 5) {
            errors.push('name');
        }

        if (trackId < 1) {
            errors.push('track');
        }

        if (gameSupportsLayouts && trackLayoutId < 1) {
            errors.push('trackLayout');
        }

        if (isTrackMaster && trackMasterCarId < 1) {
            errors.push('trackMasterCar');
        }

        setValidationErrors(errors);
        return errors.length === 0;
    };

    return (
        <>
            <Dialog visible={true} onHide={onCancelled} header={__('New Championship Event', 'sim-league-toolkit')}>
                <BusyIndicator isBusy={isLoading}/>
                <form onSubmit={onSave} noValidate>
                    <div className='flex flex-row  align-items-stretch gap-4'>
                        <div className='flex flex-column align-items-stretch gap-2' style={{minWidth: '300px'}}>
                            <label htmlFor='championship-event-name'>{__('Name', 'sim-league-toolkit')}</label>
                            <InputText id='championship-event-name' value={name}
                                       onChange={(e) => setName(e.target.value)}
                                       placeholder={__('Enter Name', 'sim-league-toolkit')}/>
                            <ValidationError
                                message={__('A name with at least 5 characters is required', 'sim-league-toolkit')}
                                show={validationErrors.includes('name')}/>

                            <label
                                htmlFor='championship-event-start-date'>{__('Start Date',
                                                                            'sim-league-toolkit')}</label>
                            <Calendar value={startDateTime} onChange={(e) => setStartDateTime(e.value)}
                                      minDate={minDate} readOnlyInput dateFormat='D, M d yy' showTime hourFormat='24'/>

                            <TrackSelector onSelectedTrackChanged={setTrackId}
                                           onSelectedTrackLayoutChanged={setTrackLayoutId} gameId={gameId}
                                           gameSupportsLayouts={gameSupportsLayouts} trackId={trackId}
                                           trackLayoutId={trackLayoutId} disabled={isLoading || isTrackMaster}
                                           isInvalid={validationErrors.includes('track') || validationErrors.includes(
                                               'trackLayout')}
                                           trackValidationMessage={__('You must select the track that will be used' +
                                                                          ' for the event.', 'sim-league-toolkit')}
                                           trackLayoutValidationMessage={__('The game supports track layouts, you' +
                                                                                ' must select a track layout that' +
                                                                                ' will be used for the event.',
                                                                            'sim-league-toolkit')}/>
                            {isTrackMaster &&
                                <CarSelector gameId={gameId}
                                             carId={trackMasterCarId}
                                             onSelectedItemChanged={(car) => setTrackMasterCarId(car.id)}
                                             disabled={isLoading}
                                             isInvalid={validationErrors.includes('trackMasterCar')}
                                             validationMessage={__(
                                                 'You must select the car being driven for this event.',
                                                 'sim-league-toolkit')}/>}
                        </div>
                    </div>
                    <SaveSubmitButton disabled={isLoading} name='submitForm'/>
                    <CancelButton onCancel={onCancelled} disabled={isLoading}/>
                </form>

            </Dialog>
        </>
    );
};

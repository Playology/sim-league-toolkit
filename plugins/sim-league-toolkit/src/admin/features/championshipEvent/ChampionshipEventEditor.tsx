import {__} from '@wordpress/i18n';
import {useState, useEffect} from '@wordpress/element';
import {FormEvent} from 'react';

import {Accordion, AccordionTab} from 'primereact/accordion';
import {Button} from 'primereact/button';
import {Calendar} from 'primereact/calendar';
import {Checkbox} from 'primereact/checkbox';
import {InputText} from 'primereact/inputtext';

import {BusyIndicator} from '../../components/BusyIndicator';
import {CarSelector} from '../game/CarSelector';
import {ChampionshipEvent, ChampionshipEventFormData, useUpdateChampionshipEvent} from '../../../features/championship';
import {ChampionshipEventTrophies} from './ChampionshipEventTrophies';
import {ChampionshipType} from '../../../enums/generated/ChampionshipType';
import {EventSessionList} from '../eventSession/EventSessionsList';
import {SaveSubmitButton} from '../../components/SaveSubmitButton';
import {TrackSelector} from '../game/TrackSelector';
import {useGames} from '../../../features/game';
import {ValidationError} from '../../components/ValidationError';

interface ChampionshipEventEditorProps {
    championshipEvent: ChampionshipEvent;
    gameId: number;
    championshipType: ChampionshipType;
    trackMasterTrackId?: number;
    trackMasterTrackLayoutId?: number;
    onCancelled: () => void;
}

const minDate = new Date();
minDate.setHours(0);
minDate.setMinutes(0);
minDate.setSeconds(0);
minDate.setMilliseconds(0);

export const ChampionshipEventEditor = ({
                                            championshipEvent,
                                            gameId,
                                            championshipType,
                                            trackMasterTrackId,
                                            trackMasterTrackLayoutId,
                                            onCancelled
                                        }: ChampionshipEventEditorProps) => {

    const {mutateAsync: updateChampionshipEvent, isPending: isLoading} = useUpdateChampionshipEvent(championshipEvent.championshipId);
    const {data: games = []} = useGames();

    const isTrackMaster = championshipType === ChampionshipType.TRACK_MASTER;

    const [activeTabIndex, setActiveTabIndex] = useState<number | number[]>(0);
    const [gameSupportsLayouts, setGameSupportsLayouts] = useState(false);
    const [isActive, setIsActive] = useState(championshipEvent.isActive);
    const [name, setName] = useState(championshipEvent.name);
    const [startDateTime, setStartDateTime] = useState(new Date(championshipEvent.startDateTime));
    const [trackId, setTrackId] = useState(isTrackMaster ? trackMasterTrackId ?? championshipEvent.trackId : championshipEvent.trackId);
    const [trackLayoutId, setTrackLayoutId] = useState(isTrackMaster ? trackMasterTrackLayoutId ?? championshipEvent.trackLayoutId : championshipEvent.trackLayoutId);
    const [trackMasterCarId, setTrackMasterCarId] = useState(championshipEvent.trackMasterCarId ?? 0);
    const [validationErrors, setValidationErrors] = useState<string[]>([]);

    useEffect(() => {
        const game = games.find(g => g.id === gameId);
        if (game) {
            setGameSupportsLayouts(game.supportsLayouts);
        }
    }, [gameId, games]);

    const onSave = async (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        if (!validate()) {
            return;
        }

        const formData: ChampionshipEventFormData = {
            isActive,
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

        await updateChampionshipEvent({id: championshipEvent.id, data: formData});
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
            <BusyIndicator isBusy={isLoading}/>
            <div className='flex align-items-center gap-2'>
                <Button icon='pi pi-arrow-left' text rounded onClick={onCancelled}
                        tooltip={__('Back to Championship', 'sim-league-toolkit')} tooltipOptions={{position: 'right'}}/>
                <h3 style={{margin: 0}}>{__('Event', 'sim-league-toolkit')} - {name}</h3>
            </div>
            <Accordion activeIndex={activeTabIndex} onTabChange={(e) => setActiveTabIndex(e.index)}>
                <AccordionTab header={__('Details', 'sim-league-toolkit')}>
                    <form onSubmit={onSave} noValidate>
                        <div className='flex flex-row  align-items-stretch gap-4'>
                            <div className='flex flex-column align-items-stretch gap-2'
                                 style={{minWidth: '300px'}}>
                                <label htmlFor='championship-event-name'>{__('Name',
                                                                             'sim-league-toolkit')}</label>
                                <InputText id='championship-event-name' value={name}
                                           onChange={(e) => setName(e.target.value)}
                                           placeholder={__('Enter Name', 'sim-league-toolkit')}/>
                                <ValidationError
                                    message={__('A name with at least 5 characters is required',
                                                'sim-league-toolkit')}
                                    show={validationErrors.includes('name')}/>

                                <label
                                    htmlFor='championship-event-start-date'>{__('Start Date',
                                                                                'sim-league-toolkit')}</label>
                                <Calendar value={startDateTime} onChange={(e) => setStartDateTime(e.value)}
                                          minDate={minDate} readOnlyInput dateFormat='D, M d yy' showTime
                                          hourFormat='24'/>

                                <TrackSelector onSelectedTrackChanged={setTrackId}
                                               onSelectedTrackLayoutChanged={setTrackLayoutId} gameId={gameId}
                                               gameSupportsLayouts={gameSupportsLayouts} trackId={trackId}
                                               trackLayoutId={trackLayoutId} disabled={isLoading || isTrackMaster}
                                               isInvalid={validationErrors.includes('track') || validationErrors.includes(
                                                   'trackLayout')}
                                               trackValidationMessage={__(
                                                   'You must select the track that will be used' +
                                                   ' for the event.',
                                                   'sim-league-toolkit')}
                                               trackLayoutValidationMessage={__(
                                                   'The game supports track layouts, you' +
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
                                <div className='flex flex-row justify-content-between'>
                                    <label
                                        htmlFor='is-active'>{__('Active', 'sim-league-toolkit')}</label>
                                    <Checkbox id='is-active' checked={isActive}
                                              onChange={(e) => setIsActive(e.checked)}
                                              style={{marginTop: '.75rem'}}/>
                                </div>
                            </div>
                        </div>
                        <SaveSubmitButton disabled={isLoading} name='submitForm'/>
                    </form>
                </AccordionTab>
                <AccordionTab header={__('Sessions', 'sim-league-toolkit')}>
                    <EventSessionList
                        eventRefId={championshipEvent.eventRefId}
                        gameId={games.find(g => g.id === gameId)?.gameKey ?? ''}
                        championshipId={championshipEvent.championshipId}
                    />
                </AccordionTab>
                <AccordionTab header={__('Trophies', 'sim-league-toolkit')}>
                    <ChampionshipEventTrophies
                        championshipEventId={championshipEvent.id}
                        championshipId={championshipEvent.championshipId}
                        trophiesAwarded={championshipEvent.trophiesAwarded}
                    />
                </AccordionTab>
            </Accordion>
        </>
    );
};

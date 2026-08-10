import {__} from '@wordpress/i18n';
import {useEffect, useState} from '@wordpress/element';
import {FormEvent} from 'react';

import {Accordion, AccordionTab} from 'primereact/accordion';
import {Calendar} from 'primereact/calendar';
import {Checkbox} from 'primereact/checkbox';
import {InputText} from 'primereact/inputtext';
import {InputNumber} from 'primereact/inputnumber';
import {InputTextarea} from 'primereact/inputtextarea';

import {BusyIndicator} from '../../components/BusyIndicator';
import {CancelButton} from '../../components/CancelButton';
import {Championship, ChampionshipEvent, ChampionshipFormData, useUpdateChampionship} from '../../../features/championship';
import {ChampionshipClasses} from './ChampionshipClasses';
import {ChampionshipEntrants} from './ChampionshipEntrants';
import {ChampionshipEvents} from '../championshipEvent/ChampionshipEvents';
import {ChampionshipTrophies} from './ChampionshipTrophies';
import {ChampionshipType, ChampionshipTypeLabels} from '../../../enums/generated/ChampionshipType';
import {PlatformSelector} from '../game/PlatformSelector';
import {RuleSetSelector} from '../ruleSet/RuleSetSelector';
import {SaveSubmitButton} from '../../components/SaveSubmitButton';
import {ScoringSetSelector} from '../scoringSet/ScoringSetSelector';
import {TrackSelector} from '../game/TrackSelector';
import {ValidationError} from '../../components/ValidationError';
import {useGames} from '../../../features/game';

interface ChampionshipEditorProps {
    onSaved: () => void;
    onCancelled: () => void;
    championship?: Championship;
    onEditEvent: (event: ChampionshipEvent) => void;
    initialActiveTabIndex?: number;
}

const minDate = new Date();

export const ChampionshipEditor = ({onSaved, onCancelled, championship, onEditEvent, initialActiveTabIndex}: ChampionshipEditorProps) => {

    const {mutateAsync: updateChampionship, isPending: isLoading} = useUpdateChampionship();
    const {data: games = [], isLoading: gamesLoading} = useGames();

    const [activeTabIndex, setActiveTabIndex] = useState<number | number[]>(initialActiveTabIndex ?? 0);
    const [allowEntryChange, setAllowEntryChange] = useState(true);
    const [bannerImageUrl, setBannerImageUrl] = useState('');
    const [championshipType, setChampionshipType] = useState<ChampionshipType>(ChampionshipType.STANDARD);
    const [description, setDescription] = useState('');
    const [entryChangeLimit, setEntryChangeLimit] = useState(1);
    const [gameId, setGameId] = useState(0);
    const [gameName] = useState(championship.game);
    const [gameSupportsLayouts, setGameSupportsLayouts] = useState(false);
    const [isActive, setIsActive] = useState<boolean>(false);
    const [maxEntrants, setMaxEntrants] = useState(0);
    const [name, setName] = useState('');
    const [platformId, setPlatformId] = useState(0);
    const [resultsToDiscard, setResultsToDiscard] = useState(0);
    const [ruleSetId, setRuleSetId] = useState(0);
    const [scoringSetId, setScoringSetId] = useState(0);
    const [startDate, setStartDate] = useState(minDate);
    const [trackMasterTrackId, setTrackMasterTrackId] = useState(0);
    const [trackMasterTrackLayoutId, setTrackMasterTrackLayoutId] = useState(0);
    const [trophiesAwarded, setTrophiesAwarded] = useState(false);
    const [validationErrors, setValidationErrors] = useState<string[]>([]);

    useEffect(() => {
        if (!championship) {
            return;
        }

        setAllowEntryChange(championship.allowEntryChange);
        setBannerImageUrl(championship.bannerImageUrl);
        setChampionshipType(championship.championshipType);
        setDescription(championship.description);
        setEntryChangeLimit(championship.entryChangeLimit);
        setGameId(championship.gameId);
        setIsActive(championship.isActive);
        setMaxEntrants(championship.maxEntrants);
        setName(championship.name);
        setPlatformId(championship.platformId);
        setResultsToDiscard(championship.resultsToDiscard);
        setRuleSetId(championship.ruleSetId);
        setScoringSetId(championship.scoringSetId);
        setStartDate(new Date(championship.startDate));
        setTrophiesAwarded(championship.trophiesAwarded);

        if (championship.championshipType === ChampionshipType.TRACK_MASTER) {
            setTrackMasterTrackId(championship.trackMasterTrackId);
            if (championship.trackMasterTrackLayoutId > 0) {
                setTrackMasterTrackLayoutId(championship.trackMasterTrackLayoutId);
            }
        }
    }, [championship]);

    useEffect(() => {
        if (gamesLoading || gameId < 1) {
            return;
        }
        const game = games.find(g => g.id === gameId);
        if (game) {
            setGameSupportsLayouts(game.supportsLayouts);
        }
    }, [gameId, games, gamesLoading]);

    const onSave = async (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        if (!validate()) {
            return;
        }

        const formData: ChampionshipFormData = {
            allowEntryChange,
            bannerImageUrl,
            championshipType,
            description,
            entryChangeLimit,
            gameId,
            isActive,
            maxEntrants,
            name,
            platformId,
            resultsToDiscard,
            ruleSetId,
            scoringSetId,
            startDate
        };

        if (championshipType === ChampionshipType.TRACK_MASTER) {
            formData.trackMasterTrackId = trackMasterTrackId;

            if (gameSupportsLayouts) {
                formData.trackMasterTrackLayoutId = trackMasterTrackLayoutId;
            }
        }

        await updateChampionship({id: championship.id, data: formData});

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

        if (scoringSetId < 1) {
            errors.push('scoringSet');
        }

        if (allowEntryChange && entryChangeLimit < 1) {
            errors.push('entryChangeLimit');
        }

        if (championshipType === ChampionshipType.TRACK_MASTER) {
            if (trackMasterTrackId < 1) {
                errors.push('trackMasterTrack');
            }

            if (gameSupportsLayouts && trackMasterTrackLayoutId < 1) {
                errors.push('trackMasterTrackLayout');
            }
        }

        setValidationErrors(errors);
        return errors.length === 0;
    };

    return (
        <>
            <BusyIndicator isBusy={isLoading}/>
            <h3>{__('Championship', 'sim-league-toolkit')} - {name}</h3>
            <h4>{__('Game', 'sim-league-toolkit')} - {gameName}</h4>
            <h4>{__('Type', 'sim-league-toolkit')} - {ChampionshipTypeLabels[championshipType]}</h4>
            <Accordion activeIndex={activeTabIndex} onTabChange={(e) => setActiveTabIndex(e.index)}>
                <AccordionTab header={__('Details', 'sim-league-toolkit')}>
                    <form onSubmit={onSave} noValidate>
                        <div className='flex flex-row flex-wrap justify-content-start gap-4'>
                            <div className='flex flex-column align-items-stretch gap-2' style={{minWidth: '350px'}}>
                                <PlatformSelector gameId={gameId}
                                                  isInvalid={validationErrors.includes('platform')}
                                                  validationMessage={__(
                                                      'You must select the platform the championship will use.',
                                                      'sim-league-toolkit')}
                                                  onSelectedItemChanged={(p) => setPlatformId(p)}
                                                  platformId={platformId}/>
                                <label htmlFor='championship-name'>{__('Name', 'sim-league-toolkit')}</label>
                                <InputText id='championship-name' value={name}
                                           onChange={(e) => setName(e.target.value)}
                                           placeholder={__('Enter Name', 'sim-league-toolkit')}/>
                                <ValidationError
                                    message={__('A name with at least 5 characters is required', 'sim-league-toolkit')}
                                    show={validationErrors.includes('name')}/>
                                <label
                                    htmlFor='championship-description'>{__('Description', 'sim-league-toolkit')}</label>
                                <InputTextarea id='championship-description' value={description}
                                               onChange={(e) => setDescription(e.target.value)}
                                               placeholder={__('Enter a Description', 'sim-league-toolkit')}
                                               rows={5} cols={40}/>
                                <ValidationError
                                    message={__(
                                        'A brief description of the championship with at least 15 characters is required.',
                                        'sim-league-toolkit')}
                                    show={validationErrors.includes('description')}/>

                                <label
                                    htmlFor='championship-start-date'>{__('Start Date', 'sim-league-toolkit')}</label>
                                <Calendar value={startDate} onChange={(e) => setStartDate(e.value)}
                                          minDate={minDate} readOnlyInput dateFormat='D, M d yy'/>

                            </div>
                            <div className='flex flex-column align-items-stretch gap-2' style={{minWidth: '350px'}}>
                                <RuleSetSelector ruleSetId={ruleSetId}
                                                 onSelectedItemChanged={(rs) => setRuleSetId(rs)}
                                                 disabled={isLoading}/>
                                <ScoringSetSelector scoringSetId={scoringSetId}
                                                    isInvalid={validationErrors.includes('scoringSet')}
                                                    validationMessage={__(
                                                        'You must select the scoring set the championship will use.')}
                                                    onSelectedItemChanged={setScoringSetId}/>
                                <label
                                    htmlFor='results-to-discard'>{__('Worst Results to Discard',
                                                                     'sim-league-toolkit')}</label>
                                <InputNumber id='results-to-discard' value={resultsToDiscard}
                                             onChange={(e) => setResultsToDiscard(e.value)}
                                             min={0}/>
                                <label
                                    htmlFor='max-entrants'>{__('Max Entrants', 'sim-league-toolkit')}</label>
                                <InputNumber id='max-entrants' value={maxEntrants}
                                             onChange={(e) => setMaxEntrants(e.value ?? 0)}
                                             min={0}/>
                                <div className='flex flex-row justify-content-between'>
                                    <label
                                        htmlFor='allow-entry-change'>{__('Allow Entry Changes',
                                                                         'sim-league-toolkit')}</label>
                                    <Checkbox id='allow-entry-change' checked={allowEntryChange}
                                              onChange={(e) => setAllowEntryChange(e.checked)}
                                              style={{marginTop: '.75rem'}}/>
                                </div>
                                {allowEntryChange && <>
                                    <label
                                        htmlFor='entry-change-limit'>{__('Entry Change Limit',
                                                                         'sim-league-toolkit')}</label>
                                    <InputNumber id='entry-change-limit' value={entryChangeLimit}
                                                 onChange={(e) => setEntryChangeLimit(e.value)}
                                                 min={1}/>
                                    <ValidationError
                                        message={__('Entry change limit must be at least 1 when entry changes are' +
                                                        ' allowed.', 'sim-league-toolkit')}
                                        show={validationErrors.includes('entryChangeLimit')}/>
                                </>}
                                {championshipType === ChampionshipType.TRACK_MASTER &&
                                    <TrackSelector gameId={gameId}
                                                   gameSupportsLayouts={gameSupportsLayouts}
                                                   trackId={trackMasterTrackId}
                                                   trackLayoutId={trackMasterTrackLayoutId}
                                                   isInvalid={validationErrors.includes('trackMasterTrack') || validationErrors.includes(
                                                       'trackMasterTrackLayout')}
                                                   disabled={isLoading}
                                                   onSelectedTrackChanged={setTrackMasterTrackId}
                                                   onSelectedTrackLayoutChanged={setTrackMasterTrackLayoutId}
                                                   trackValidationMessage={__(
                                                       'When the championship type is track master you must select the track that will be used for all events.',
                                                       'sim-league-toolkit')}
                                                   trackLayoutValidationMessage={__(
                                                       'The game supports track layouts, you must select a track layout that will be used for all events.',
                                                       'sim-league-toolkit')}
                                    />}
                                <div className='flex flex-row justify-content-between'>
                                    <label
                                        htmlFor='is-active'>{__('Active', 'sim-league-toolkit')}</label>
                                    <Checkbox id='is-active' checked={isActive}
                                              onChange={(e) => setIsActive(e.checked)}
                                              style={{marginTop: '.75rem'}}/>
                                </div>
                            </div>
                        </div>
                        <SaveSubmitButton disabled={isLoading || trophiesAwarded} name='submitForm'/>
                        <CancelButton onCancel={onCancelled} disabled={isLoading || trophiesAwarded}/>
                    </form>
                </AccordionTab>
                <AccordionTab header={__('Classes', 'sim-league-toolkit')}>
                    <ChampionshipClasses championshipId={championship.id}/>
                </AccordionTab>
                <AccordionTab header={__('Events', 'sim-league-toolkit')}>
                    <ChampionshipEvents championshipId={championship.id} gameId={gameId}
                                        championshipType={championshipType}
                                        trackMasterTrackId={trackMasterTrackId}
                                        trackMasterTrackLayoutId={trackMasterTrackLayoutId}
                                        onEditEvent={onEditEvent}/>
                </AccordionTab>
                <AccordionTab header={__('Server', 'sim-league-toolkit')}>
                </AccordionTab>
                <AccordionTab header={__('Entrants', 'sim-league-toolkit')}>
                    <ChampionshipEntrants championshipId={championship.id} gameId={gameId}/>
                </AccordionTab>
                <AccordionTab header={__('Standings', 'sim-league-toolkit')}>
                    <ChampionshipTrophies championshipId={championship.id} trophiesAwarded={trophiesAwarded}/>
                </AccordionTab>
            </Accordion>
        </>
    );
};
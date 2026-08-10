import {__} from '@wordpress/i18n';
import {useEffect, useState} from '@wordpress/element';

import {Button} from 'primereact/button';
import {Calendar} from 'primereact/calendar';
import {InputNumber} from 'primereact/inputnumber';
import {InputText} from 'primereact/inputtext';
import {InputTextarea} from 'primereact/inputtextarea';

import {ChampionshipBuilderFormData} from '../../../features/championshipBuilder';
import {ChampionshipType} from '../../../enums/generated/ChampionshipType';
import {ChampionshipTypeSelector} from '../championship/ChampionshipTypeSelector';
import {GameSelector} from '../game/GameSelector';
import {PlatformSelector} from '../game/PlatformSelector';
import {RuleSetSelector} from '../ruleSet/RuleSetSelector';
import {ScoringSetSelector} from '../scoringSet/ScoringSetSelector';
import {useGames} from '../../../features/game';
import {ValidationError} from '../../components/ValidationError';

interface BuilderDetailsStepProps {
    formData: ChampionshipBuilderFormData;
    onChange: (updates: Partial<ChampionshipBuilderFormData>) => void;
    onNext: () => void;
}

const minDate = new Date();

export const BuilderDetailsStep = ({formData, onChange, onNext}: BuilderDetailsStepProps) => {
    const {data: games = []} = useGames();

    const [gameSupportsLayouts, setGameSupportsLayouts] = useState(false);
    const [validationErrors, setValidationErrors] = useState<string[]>([]);

    const isTrackMaster = formData.championshipType === ChampionshipType.TRACK_MASTER;

    useEffect(() => {
        if (formData.gameId < 1) {
            return;
        }
        const game = games.find(g => g.id === formData.gameId);
        if (game) {
            setGameSupportsLayouts(game.supportsLayouts);
        }
    }, [formData.gameId, games]);

    const onSelectedGameChanged = (gameId: number) => {
        onChange({gameId, platformId: 0});
    };

    const validate = () => {
        const errors: string[] = [];

        if (formData.gameId < 1) {
            errors.push('game');
        }

        if (formData.platformId < 1) {
            errors.push('platform');
        }

        if (!formData.name || formData.name.length < 5) {
            errors.push('name');
        }

        if (!formData.description || formData.description.length < 15) {
            errors.push('description');
        }

        if (formData.scoringSetId < 1) {
            errors.push('scoringSet');
        }

        if (formData.eventStartIntervalDays < 1) {
            errors.push('eventStartIntervalDays');
        }

        if (!isTrackMaster && formData.entryChangeLimit < 1) {
            errors.push('entryChangeLimit');
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
            <GameSelector gameId={formData.gameId}
                          isInvalid={validationErrors.includes('game')}
                          validationMessage={__('You must select the game that this championship will use.',
                                                'sim-league-toolkit')}
                          onSelectedItemChanged={onSelectedGameChanged}/>
            {formData.gameId !== 0 &&
                <div className='flex flex-row flex-wrap justify-content-start gap-4 mt-2'>
                    <div className='flex flex-column align-items-stretch gap-2' style={{minWidth: '350px'}}>
                        <PlatformSelector gameId={formData.gameId}
                                          isInvalid={validationErrors.includes('platform')}
                                          validationMessage={__(
                                              'You must select the platform the championship will use.',
                                              'sim-league-toolkit')}
                                          onSelectedItemChanged={(platformId) => onChange({platformId})}
                                          platformId={formData.platformId}/>
                        <label htmlFor='builder-name'>{__('Name', 'sim-league-toolkit')}</label>
                        <InputText id='builder-name' value={formData.name}
                                   onChange={(e) => onChange({name: e.target.value})}
                                   placeholder={__('Enter Name', 'sim-league-toolkit')}/>
                        <ValidationError
                            message={__('A name with at least 5 characters is required', 'sim-league-toolkit')}
                            show={validationErrors.includes('name')}/>
                        <label htmlFor='builder-description'>{__('Description', 'sim-league-toolkit')}</label>
                        <InputTextarea id='builder-description' value={formData.description}
                                       onChange={(e) => onChange({description: e.target.value})}
                                       placeholder={__('Enter a Description', 'sim-league-toolkit')}
                                       rows={7} cols={45}/>
                        <ValidationError
                            message={__(
                                'A brief description of the championship with at least 15 characters is required.',
                                'sim-league-toolkit')}
                            show={validationErrors.includes('description')}/>
                        <label htmlFor='builder-start-date'>{__('Start Date', 'sim-league-toolkit')}</label>
                        <Calendar id='builder-start-date' value={formData.startDate}
                                  onChange={(e) => onChange({startDate: e.value as Date})}
                                  minDate={minDate} readOnlyInput dateFormat='D, M d yy'/>
                        <ChampionshipTypeSelector championshipType={formData.championshipType}
                                                  onSelectedItemChanged={(championshipType) => onChange(
                                                      {championshipType})}/>
                    </div>
                    <div className='flex flex-column align-items-stretch gap-2' style={{minWidth: '350px'}}>
                        <RuleSetSelector ruleSetId={formData.ruleSetId}
                                         onSelectedItemChanged={(ruleSetId) => onChange({ruleSetId})}/>
                        <ScoringSetSelector scoringSetId={formData.scoringSetId}
                                            isInvalid={validationErrors.includes('scoringSet')}
                                            validationMessage={__(
                                                'You must select the scoring set the championship will use.',
                                                'sim-league-toolkit')}
                                            onSelectedItemChanged={(scoringSetId) => onChange({scoringSetId})}/>
                        <label htmlFor='builder-results-to-discard'>{__('Worst Results to Discard',
                                                                         'sim-league-toolkit')}</label>
                        <InputNumber id='builder-results-to-discard' value={formData.resultsToDiscard}
                                     onChange={(e) => onChange({resultsToDiscard: e.value ?? 0})} min={0}/>
                        <label htmlFor='builder-max-entrants'>{__('Max Entrants', 'sim-league-toolkit')}</label>
                        <InputNumber id='builder-max-entrants' value={formData.maxEntrants}
                                     onChange={(e) => onChange({maxEntrants: e.value ?? 0})} min={0}/>
                        {!isTrackMaster && <>
                            <label htmlFor='builder-entry-change-limit'>{__('Entry Change Limit',
                                                                             'sim-league-toolkit')}</label>
                            <InputNumber id='builder-entry-change-limit' value={formData.entryChangeLimit}
                                         onChange={(e) => onChange({entryChangeLimit: e.value ?? 1})} min={1}/>
                            <ValidationError message={__('An entry change limit of at least 1 is required.',
                                                          'sim-league-toolkit')}
                                             show={validationErrors.includes('entryChangeLimit')}/>
                        </>}
                        <label htmlFor='builder-event-start-time'>{__('Event Start Time', 'sim-league-toolkit')}</label>
                        <Calendar id='builder-event-start-time'
                                  value={(() => {
                                      const [hours, minutes] = formData.eventStartTime.split(':').map(Number);
                                      const time = new Date();
                                      time.setHours(hours || 0, minutes || 0, 0, 0);
                                      return time;
                                  })()}
                                  onChange={(e) => {
                                      const time = e.value as Date;
                                      const eventStartTime = `${String(time.getHours()).padStart(2, '0')}:${String(
                                          time.getMinutes()).padStart(2, '0')}`;
                                      onChange({eventStartTime});
                                  }}
                                  timeOnly hourFormat='24'/>
                        <label htmlFor='builder-event-start-interval'>{__('Days Between Events',
                                                                           'sim-league-toolkit')}</label>
                        <InputNumber id='builder-event-start-interval' value={formData.eventStartIntervalDays}
                                     onChange={(e) => onChange({eventStartIntervalDays: e.value ?? 7})} min={1}
                                     max={30}/>
                        <ValidationError message={__('A start interval of at least 1 day is required.',
                                                      'sim-league-toolkit')}
                                         show={validationErrors.includes('eventStartIntervalDays')}/>
                    </div>
                </div>
            }
            <div className='mt-4'>
                <Button label={__('Next', 'sim-league-toolkit')} onClick={onClickNext}
                        disabled={formData.gameId === 0}/>
            </div>
        </>
    );
};

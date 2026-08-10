import {__} from '@wordpress/i18n';
import {useState} from '@wordpress/element';

import {Button} from 'primereact/button';
import {Checkbox} from 'primereact/checkbox';
import {InputNumber} from 'primereact/inputnumber';

import {ChampionshipBuilderFormData, ChampionshipBuilderRoundFormData} from '../../../features/championshipBuilder';
import {useAllTrackLayouts, useGame, useLastChampionshipTrackIds, useTracks} from '../../../features/game';
import {ValidationError} from '../../components/ValidationError';

interface BuilderTracksStepProps {
    formData: ChampionshipBuilderFormData;
    onChange: (updates: Partial<ChampionshipBuilderFormData>) => void;
    onNext: () => void;
    onBack: () => void;
}

const shuffle = <T, >(items: T[]): T[] => {
    const shuffled = [...items];
    for (let i = shuffled.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
    }
    return shuffled;
};

export const BuilderTracksStep = ({formData, onChange, onNext, onBack}: BuilderTracksStepProps) => {
    const {data: game} = useGame(formData.gameId);
    const gameSupportsLayouts = game?.supportsLayouts ?? false;

    const {data: tracks = []} = useTracks(formData.gameId);
    const {data: layouts = []} = useAllTrackLayouts(formData.gameId);
    const {data: lastChampionshipTrackIds = []} = useLastChampionshipTrackIds(formData.gameId);

    const [minLength, setMinLength] = useState(0);
    const [maxLength, setMaxLength] = useState(0);
    const [excludeDlc, setExcludeDlc] = useState(false);
    const [excludeLastChampionshipTracks, setExcludeLastChampionshipTracks] = useState(false);
    const [validationErrors, setValidationErrors] = useState<string[]>([]);

    const toggleRound = (round: ChampionshipBuilderRoundFormData, checked: boolean) => {
        if (checked) {
            onChange({rounds: [...formData.rounds, round]});
            return;
        }

        onChange({
            rounds: formData.rounds.filter(r => !(r.trackId === round.trackId && r.trackLayoutId === round.trackLayoutId)),
        });
    };

    const onShuffle = () => {
        onChange({rounds: shuffle(formData.rounds)});
    };

    const validate = () => {
        const errors: string[] = [];

        if (formData.rounds.length < 2) {
            errors.push('rounds');
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

    const renderLayoutCandidates = () => {
        let candidates = layouts;

        if (minLength > 0) {
            candidates = candidates.filter(l => l.length >= minLength);
        }
        if (maxLength > 0) {
            candidates = candidates.filter(l => l.length <= maxLength);
        }
        if (excludeDlc) {
            candidates = candidates.filter(l => !l.dlcPack);
        }
        if (excludeLastChampionshipTracks) {
            candidates = candidates.filter(l => !lastChampionshipTrackIds.includes(l.trackId));
        }

        return (
            <ul className='list-none p-0'>
                {candidates.map(layout => {
                    const checked = formData.rounds.some(r => r.trackLayoutId === layout.id);
                    return (
                        <li key={layout.id} className='flex align-items-center gap-2 mb-1'>
                            <Checkbox inputId={`builder-layout-${layout.id}`} checked={checked}
                                      onChange={(e) => toggleRound({
                                          trackId: layout.trackId,
                                          trackLayoutId: layout.id
                                      }, e.checked)}/>
                            <label htmlFor={`builder-layout-${layout.id}`}>
                                {layout.track} — {layout.name} ({(layout.length / 1000).toFixed(2)}km)
                            </label>
                        </li>
                    );
                })}
            </ul>
        );
    };

    const renderTrackCandidates = () => {
        let candidates = tracks;

        if (excludeLastChampionshipTracks) {
            candidates = candidates.filter(t => !lastChampionshipTrackIds.includes(t.id));
        }

        return (
            <ul className='list-none p-0'>
                {candidates.map(track => {
                    const checked = formData.rounds.some(r => r.trackId === track.id);
                    return (
                        <li key={track.id} className='flex align-items-center gap-2 mb-1'>
                            <Checkbox inputId={`builder-track-${track.id}`} checked={checked}
                                      onChange={(e) => toggleRound({trackId: track.id}, e.checked)}/>
                            <label htmlFor={`builder-track-${track.id}`}>{track.shortName}</label>
                        </li>
                    );
                })}
            </ul>
        );
    };

    return (
        <>
            <p>{__('Select the tracks the Championship rounds will use.', 'sim-league-toolkit')}</p>

            <fieldset>
                <legend>{__('Filters', 'sim-league-toolkit')}</legend>
                {gameSupportsLayouts && <>
                    <div className='flex flex-row align-items-center gap-2 mb-2'>
                        <label htmlFor='builder-min-length'>{__('Min Length (m)', 'sim-league-toolkit')}</label>
                        <InputNumber id='builder-min-length' value={minLength}
                                     onChange={(e) => setMinLength(e.value ?? 0)} min={0} step={100}/>
                        <label htmlFor='builder-max-length'>{__('Max Length (m)', 'sim-league-toolkit')}</label>
                        <InputNumber id='builder-max-length' value={maxLength}
                                     onChange={(e) => setMaxLength(e.value ?? 0)} min={0} step={100}/>
                    </div>
                    <div className='flex align-items-center gap-2 mb-2'>
                        <Checkbox inputId='builder-exclude-dlc' checked={excludeDlc}
                                  onChange={(e) => setExcludeDlc(e.checked)}/>
                        <label htmlFor='builder-exclude-dlc'>{__('Show base game tracks only',
                                                                   'sim-league-toolkit')}</label>
                    </div>
                </>}
                <div className='flex align-items-center gap-2'>
                    <Checkbox inputId='builder-exclude-last' checked={excludeLastChampionshipTracks}
                              onChange={(e) => setExcludeLastChampionshipTracks(e.checked)}/>
                    <label htmlFor='builder-exclude-last'>{__(
                        'Hide tracks used in the last championship for this game', 'sim-league-toolkit')}</label>
                </div>
            </fieldset>

            <fieldset className='mt-2'>
                <legend>{__('Tracks', 'sim-league-toolkit')}</legend>
                {gameSupportsLayouts ? renderLayoutCandidates() : renderTrackCandidates()}
            </fieldset>
            <ValidationError message={__('You must select at least two Tracks to form a Championship.',
                                          'sim-league-toolkit')}
                             show={validationErrors.includes('rounds')}/>

            {formData.rounds.length > 0 && <>
                <h4>{__('Round Order', 'sim-league-toolkit')}</h4>
                <ol>
                    {formData.rounds.map((round, index) => {
                        const label = gameSupportsLayouts
                            ? layouts.find(l => l.id === round.trackLayoutId)?.track
                            : tracks.find(t => t.id === round.trackId)?.shortName;
                        return <li key={index}>{label}</li>;
                    })}
                </ol>
                <Button label={__('Shuffle Order', 'sim-league-toolkit')} severity='secondary' onClick={onShuffle}/>
            </>}

            <div className='mt-4'>
                <Button label={__('Back', 'sim-league-toolkit')} severity='secondary' onClick={onBack}/>
                <Button label={__('Next', 'sim-league-toolkit')} onClick={onClickNext}
                        style={{marginLeft: '.5rem'}}/>
            </div>
        </>
    );
};

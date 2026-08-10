import {__} from '@wordpress/i18n';
import {useState} from '@wordpress/element';

import {Button} from 'primereact/button';

import {ChampionshipBuilderFormData} from '../../../features/championshipBuilder';
import {TrackSelector} from '../game/TrackSelector';
import {useGame} from '../../../features/game';

interface BuilderTrackMasterTrackStepProps {
    formData: ChampionshipBuilderFormData;
    onChange: (updates: Partial<ChampionshipBuilderFormData>) => void;
    onNext: () => void;
    onBack: () => void;
}

export const BuilderTrackMasterTrackStep = ({formData, onChange, onNext, onBack}: BuilderTrackMasterTrackStepProps) => {
    const {data: game} = useGame(formData.gameId);
    const gameSupportsLayouts = game?.supportsLayouts ?? false;

    const [isInvalid, setIsInvalid] = useState(false);

    const validate = () => {
        const valid = !!formData.trackMasterTrackId && (!gameSupportsLayouts || !!formData.trackMasterTrackLayoutId);
        setIsInvalid(!valid);
        return valid;
    };

    const onClickNext = () => {
        if (!validate()) {
            return;
        }
        onNext();
    };

    return (
        <>
            <p>{__('Select the fixed track that every round of this Championship will use.',
                   'sim-league-toolkit')}</p>
            <TrackSelector gameId={formData.gameId} gameSupportsLayouts={gameSupportsLayouts}
                           trackId={formData.trackMasterTrackId} trackLayoutId={formData.trackMasterTrackLayoutId}
                           isInvalid={isInvalid}
                           onSelectedTrackChanged={(trackMasterTrackId) => onChange(
                               {trackMasterTrackId, trackMasterTrackLayoutId: undefined})}
                           onSelectedTrackLayoutChanged={(trackMasterTrackLayoutId) => onChange(
                               {trackMasterTrackLayoutId})}
                           trackValidationMessage={__('You must select a track for the Championship.',
                                                       'sim-league-toolkit')}
                           trackLayoutValidationMessage={__(
                               'The game supports track layouts, you must select a track layout that will be used' +
                               ' for all events.', 'sim-league-toolkit')}/>
            <div className='mt-4'>
                <Button label={__('Back', 'sim-league-toolkit')} severity='secondary' onClick={onBack}/>
                <Button label={__('Next', 'sim-league-toolkit')} onClick={onClickNext}
                        style={{marginLeft: '.5rem'}}/>
            </div>
        </>
    );
};

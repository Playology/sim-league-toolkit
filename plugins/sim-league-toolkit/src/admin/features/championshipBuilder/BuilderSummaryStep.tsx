import {__} from '@wordpress/i18n';

import {Button} from 'primereact/button';

import {ChampionshipBuilderFormData} from '../../../features/championshipBuilder';
import {ChampionshipType} from '../../../enums/generated/ChampionshipType';
import {SessionType, SessionTypeLabels} from '../../../enums/generated/SessionType';
import {useAllTrackLayouts, useCars, useGame, useTracks} from '../../../features/game';
import {useEventClassesByGame} from '../../../features/eventClass';

interface BuilderSummaryStepProps {
    formData: ChampionshipBuilderFormData;
    onBack: () => void;
    onFinish: () => void;
    isSubmitting: boolean;
}

const calculateRoundStartDate = (formData: ChampionshipBuilderFormData, roundIndex: number): Date => {
    const date = new Date(formData.startDate);
    date.setDate(date.getDate() + roundIndex * formData.eventStartIntervalDays);
    const [hours, minutes] = formData.eventStartTime.split(':').map(Number);
    date.setHours(hours || 0, minutes || 0, 0, 0);
    return date;
};

export const BuilderSummaryStep = ({formData, onBack, onFinish, isSubmitting}: BuilderSummaryStepProps) => {
    const {data: game} = useGame(formData.gameId);
    const {data: existingClasses = []} = useEventClassesByGame(formData.gameId);
    const {data: tracks = []} = useTracks(formData.gameId);
    const {data: layouts = []} = useAllTrackLayouts(formData.gameId);
    const {data: cars = []} = useCars(formData.gameId);

    const isTrackMaster = formData.championshipType === ChampionshipType.TRACK_MASTER;

    const classLabel = (index: number) => {
        const classPlan = formData.classes[index];
        if (classPlan.eventClassId) {
            return existingClasses.find(c => c.id === classPlan.eventClassId)?.name ?? '';
        }
        return classPlan.name;
    };

    const roundLabel = (roundIndex: number) => {
        const round = formData.rounds[roundIndex];

        if (isTrackMaster) {
            const car = cars.find(c => c.id === round.carId);
            return car ? `${car.name} (${car.year})` : '';
        }

        if (round.trackLayoutId) {
            const layout = layouts.find(l => l.id === round.trackLayoutId);
            return layout ? `${layout.track} — ${layout.name}` : '';
        }

        return tracks.find(t => t.id === round.trackId)?.shortName ?? '';
    };

    const trackMasterTrackLabel = () => {
        if (formData.trackMasterTrackLayoutId) {
            const layout = layouts.find(l => l.id === formData.trackMasterTrackLayoutId);
            if (layout) {
                return `${layout.track} — ${layout.name}`;
            }
        }
        return tracks.find(t => t.id === formData.trackMasterTrackId)?.shortName ?? '';
    };

    return (
        <>
            <fieldset>
                <legend>{__('Championship', 'sim-league-toolkit')}</legend>
                <p><strong>{__('Name', 'sim-league-toolkit')}:</strong> {formData.name}</p>
                <p><strong>{__('Game', 'sim-league-toolkit')}:</strong> {game?.name}</p>
                <p><strong>{__('Type', 'sim-league-toolkit')}:</strong> {isTrackMaster
                    ? __('Track Master', 'sim-league-toolkit') : __('Standard', 'sim-league-toolkit')}</p>
                {isTrackMaster &&
                    <p><strong>{__('Track', 'sim-league-toolkit')}:</strong> {trackMasterTrackLabel()}</p>}
            </fieldset>

            <fieldset>
                <legend>{__('Classes', 'sim-league-toolkit')}</legend>
                {formData.classes.map((_, index) => <p key={index}>{classLabel(index)}</p>)}
            </fieldset>

            <fieldset>
                <legend>{__('Rounds', 'sim-league-toolkit')}</legend>
                {formData.rounds.map((_, index) => (
                    <p key={index}>
                        {index + 1}. {formData.name} - {__('Round', 'sim-league-toolkit')} {index + 1} — {roundLabel(
                        index)} — {calculateRoundStartDate(formData, index).toLocaleDateString()}
                    </p>
                ))}
            </fieldset>

            <fieldset>
                <legend>{__('Sessions per round', 'sim-league-toolkit')}</legend>
                {formData.sessionTemplates.filter(t => t.count > 0).map(t => (
                    <p key={t.sessionType}>{SessionTypeLabels[t.sessionType as SessionType]}: {t.count}</p>
                ))}
            </fieldset>

            <div className='mt-4'>
                <Button label={__('Back', 'sim-league-toolkit')} severity='secondary' onClick={onBack}
                        disabled={isSubmitting}/>
                <Button label={__('Finish', 'sim-league-toolkit')} onClick={onFinish} loading={isSubmitting}
                        style={{marginLeft: '.5rem'}}/>
            </div>
        </>
    );
};

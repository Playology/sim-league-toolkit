import {__} from '@wordpress/i18n';
import {useState} from '@wordpress/element';

import {MenuItem} from 'primereact/menuitem';
import {Steps} from 'primereact/steps';

import {BusyIndicator} from '../../components/BusyIndicator';
import {CancelButton} from '../../components/CancelButton';
import {ChampionshipType} from '../../../enums/generated/ChampionshipType';
import {ChampionshipBuilderFormData, useBuildChampionship} from '../../../features/championshipBuilder';
import {BuilderClassesStep} from './BuilderClassesStep';
import {BuilderDetailsStep} from './BuilderDetailsStep';
import {BuilderSessionsStep} from './BuilderSessionsStep';
import {BuilderSummaryStep} from './BuilderSummaryStep';
import {BuilderTrackMasterRoundsStep} from './BuilderTrackMasterRoundsStep';
import {BuilderTrackMasterTrackStep} from './BuilderTrackMasterTrackStep';
import {BuilderTracksStep} from './BuilderTracksStep';

interface ChampionshipBuilderWizardProps {
    onSaved: (championshipId: number) => void;
    onCancelled: () => void;
    initialFormData?: Partial<ChampionshipBuilderFormData>;
}

type StepKind = 'details' | 'classes' | 'tracks' | 'trackMasterTrack' | 'trackMasterRounds' | 'sessions' | 'summary';

const standardSteps: StepKind[] = ['details', 'classes', 'tracks', 'sessions', 'summary'];
const trackMasterSteps: StepKind[] = ['details', 'classes', 'trackMasterTrack', 'trackMasterRounds', 'sessions', 'summary'];

const stepLabels: Record<StepKind, string> = {
    details: __('Details', 'sim-league-toolkit'),
    classes: __('Classes', 'sim-league-toolkit'),
    tracks: __('Tracks', 'sim-league-toolkit'),
    trackMasterTrack: __('Track', 'sim-league-toolkit'),
    trackMasterRounds: __('Rounds', 'sim-league-toolkit'),
    sessions: __('Sessions', 'sim-league-toolkit'),
    summary: __('Summary', 'sim-league-toolkit'),
};

const createDefaultFormData = (): ChampionshipBuilderFormData => ({
    name: '',
    description: '',
    gameId: 0,
    platformId: 0,
    championshipType: ChampionshipType.STANDARD,
    startDate: new Date(),
    ruleSetId: 0,
    scoringSetId: 0,
    resultsToDiscard: 0,
    entryChangeLimit: 1,
    maxEntrants: 0,
    eventStartTime: '14:00',
    eventStartIntervalDays: 7,
    classes: [],
    rounds: [],
    sessionTemplates: [],
});

export const ChampionshipBuilderWizard = ({onSaved, onCancelled, initialFormData}: ChampionshipBuilderWizardProps) => {
    const {mutateAsync: buildChampionship, isPending: isBuilding} = useBuildChampionship();

    const [stepIndex, setStepIndex] = useState(0);
    const [formData, setFormData] = useState<ChampionshipBuilderFormData>({...createDefaultFormData(), ...initialFormData});

    const isTrackMaster = formData.championshipType === ChampionshipType.TRACK_MASTER;
    const steps = isTrackMaster ? trackMasterSteps : standardSteps;
    const currentStep = steps[stepIndex];

    const updateFormData = (updates: Partial<ChampionshipBuilderFormData>) => {
        setFormData(prev => ({...prev, ...updates}));
    };

    const goNext = () => setStepIndex(index => Math.min(index + 1, steps.length - 1));
    const goBack = () => setStepIndex(index => Math.max(index - 1, 0));

    const onFinish = async () => {
        const championshipId = await buildChampionship(formData);
        onSaved(championshipId);
    };

    const menuItems: MenuItem[] = steps.map(kind => ({label: stepLabels[kind]}));

    const renderStep = () => {
        switch (currentStep) {
            case 'details':
                return <BuilderDetailsStep formData={formData} onChange={updateFormData} onNext={goNext}/>;
            case 'classes':
                return <BuilderClassesStep formData={formData} onChange={updateFormData} onNext={goNext}
                                            onBack={goBack}/>;
            case 'tracks':
                return <BuilderTracksStep formData={formData} onChange={updateFormData} onNext={goNext}
                                           onBack={goBack}/>;
            case 'trackMasterTrack':
                return <BuilderTrackMasterTrackStep formData={formData} onChange={updateFormData} onNext={goNext}
                                                     onBack={goBack}/>;
            case 'trackMasterRounds':
                return <BuilderTrackMasterRoundsStep formData={formData} onChange={updateFormData} onNext={goNext}
                                                      onBack={goBack}/>;
            case 'sessions':
                return <BuilderSessionsStep formData={formData} onChange={updateFormData} onNext={goNext}
                                             onBack={goBack}/>;
            case 'summary':
                return <BuilderSummaryStep formData={formData} onBack={goBack} onFinish={onFinish}
                                            isSubmitting={isBuilding}/>;
        }
    };

    return (
        <>
            <BusyIndicator isBusy={isBuilding}/>
            <h3>{__('Build Championship', 'sim-league-toolkit')}</h3>
            <Steps model={menuItems} activeIndex={stepIndex} readOnly={true} className='mb-4'/>
            {renderStep()}
            <CancelButton onCancel={onCancelled} disabled={isBuilding}/>
        </>
    );
};

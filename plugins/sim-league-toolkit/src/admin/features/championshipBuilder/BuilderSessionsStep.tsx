import {__} from '@wordpress/i18n';
import {useEffect, useState} from '@wordpress/element';

import {Button} from 'primereact/button';
import {InputNumber} from 'primereact/inputnumber';

import {
    ChampionshipBuilderFormData,
    ChampionshipBuilderSessionTemplateFormData,
} from '../../../features/championshipBuilder';
import {SessionAttributeFields} from '../eventSession/SessionAttributeFields';
import {SessionType, SessionTypeLabels} from '../../../enums/generated/SessionType';
import {useGame, useGameConfig} from '../../../features/game';
import {ValidationError} from '../../components/ValidationError';

interface BuilderSessionsStepProps {
    formData: ChampionshipBuilderFormData;
    onChange: (updates: Partial<ChampionshipBuilderFormData>) => void;
    onNext: () => void;
    onBack: () => void;
}

export const BuilderSessionsStep = ({formData, onChange, onNext, onBack}: BuilderSessionsStepProps) => {
    const {data: game} = useGame(formData.gameId);
    const {data: gameConfig} = useGameConfig(game?.gameKey ?? '');
    const [validationErrors, setValidationErrors] = useState<string[]>([]);

    useEffect(() => {
        if (!gameConfig || formData.sessionTemplates.length > 0) {
            return;
        }

        const templates: ChampionshipBuilderSessionTemplateFormData[] = Object.keys(gameConfig.sessionTypes).map(
            sessionType => {
                const fields = gameConfig.sessionTypes[sessionType].fields;
                const attributes: Record<string, unknown> = {};
                fields.forEach(field => {
                    attributes[field.key] = field.default ?? null;
                });

                return {
                    sessionType: sessionType as SessionType,
                    count: sessionType === SessionType.RACE ? 1 : 0,
                    attributes,
                };
            });

        onChange({sessionTemplates: templates});
    }, [gameConfig]);

    const updateTemplate = (sessionType: SessionType, updates: Partial<ChampionshipBuilderSessionTemplateFormData>) => {
        onChange({
            sessionTemplates: formData.sessionTemplates.map(
                t => t.sessionType === sessionType ? {...t, ...updates} : t),
        });
    };

    const validate = () => {
        const errors: string[] = [];

        const raceCount = formData.sessionTemplates
            .filter(t => t.sessionType === SessionType.RACE)
            .reduce((sum, t) => sum + t.count, 0);

        if (raceCount === 0) {
            errors.push('race');
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

    if (!gameConfig) {
        return null;
    }

    return (
        <>
            <p>{__('Configure the Sessions each round of the Championship will have.', 'sim-league-toolkit')}</p>
            {formData.sessionTemplates.map(template => {
                const fields = gameConfig.sessionTypes[template.sessionType]?.fields ?? [];
                return (
                    <fieldset key={template.sessionType} className='mb-3'>
                        <legend>{SessionTypeLabels[template.sessionType]}</legend>
                        <div className='flex align-items-center gap-2 mb-2'>
                            <label htmlFor={`builder-session-count-${template.sessionType}`}>
                                {__('Sessions per round', 'sim-league-toolkit')}
                            </label>
                            <InputNumber id={`builder-session-count-${template.sessionType}`} value={template.count}
                                         onValueChange={(e) => updateTemplate(template.sessionType,
                                                                              {count: e.value ?? 0})} min={0}/>
                        </div>
                        {template.count > 0 && fields.length > 0 &&
                            <SessionAttributeFields fields={fields} values={template.attributes}
                                                     onChange={(key, value) => updateTemplate(template.sessionType, {
                                                         attributes: {...template.attributes, [key]: value},
                                                     })}/>}
                    </fieldset>
                );
            })}
            <ValidationError message={__('At least one Race session must be configured.', 'sim-league-toolkit')}
                             show={validationErrors.includes('race')}/>

            <div className='mt-4'>
                <Button label={__('Back', 'sim-league-toolkit')} severity='secondary' onClick={onBack}/>
                <Button label={__('Next', 'sim-league-toolkit')} onClick={onClickNext}
                        style={{marginLeft: '.5rem'}}/>
            </div>
        </>
    );
};

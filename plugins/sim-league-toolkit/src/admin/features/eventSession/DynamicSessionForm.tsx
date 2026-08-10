import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';

import { Button } from 'primereact/button';
import { Dropdown } from 'primereact/dropdown';
import { InputText } from 'primereact/inputtext';

import {GameConfig} from '../../../features/game';
import {EventSessionFormData} from '../../../features/eventSession';
import {SessionAttributeFields} from './SessionAttributeFields';
import {SessionType, SessionTypeOptions} from '../../../enums/generated/SessionType';

interface DynamicSessionFormProps {
    eventRefId: number;
    gameId: string;
    gameConfig: GameConfig | null;
    initialData?: Partial<EventSessionFormData>;
    onSubmit: (data: EventSessionFormData) => Promise<void>;
    onCancel: () => void;
    loading?: boolean;
}

export const DynamicSessionForm = ({
                                       eventRefId,
                                       gameId,
                                       gameConfig,
                                       initialData,
                                       onSubmit,
                                       onCancel,
                                       loading = false,
                                   }: DynamicSessionFormProps) => {
    const [name, setName] = useState(initialData?.name ?? '');
    const [sessionType, setSessionType] = useState<SessionType>(
        initialData?.sessionType ?? SessionType.PRACTICE
    );
    const [attributes, setAttributes] = useState<Record<string, unknown>>(
        initialData?.attributes ?? {}
    );

    const sessionTypeConfig = gameConfig?.sessionTypes?.[sessionType];
    const fields = sessionTypeConfig?.fields ?? [];

    useEffect(() => {
        const defaults: Record<string, unknown> = {};

        fields.forEach((field) => {
            defaults[field.key] = initialData?.attributes?.[field.key] ?? field.default ?? null;
        });

        setAttributes(defaults);
    }, [sessionType, gameConfig]);

    const handleAttributeChange = (key: string, value: unknown) => {
        setAttributes((prev) => ({
            ...prev,
            [key]: value,
        }));
    };

    const handleSubmit = async () => {
        const data: EventSessionFormData = {
            eventRefId,
            gameId,
            name,
            sessionType,
            sortOrder: initialData?.sortOrder ?? 0,
            attributes,
        };

        await onSubmit(data);
    };

    return (
        <div className="dynamic-session-form">
            <div className="field">
                <label htmlFor="name">{__('Session Name', 'sim-league-toolkit')}</label>
                <InputText
                    id="name"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    className="w-full"
                />
            </div>

            <div className="field">
                <label htmlFor="sessionType">{__('Session Type', 'sim-league-toolkit')}</label>
                <Dropdown
                    id="sessionType"
                    value={sessionType}
                    options={[...SessionTypeOptions]}
                    optionLabel="label"
                    optionValue="value"
                    onChange={(e) => setSessionType(e.value)}
                    className="w-full"
                />
            </div>

            {fields.length > 0 && (
                <div className="game-specific-fields mt-4">
                    <h4>{__('Game-Specific Settings', 'sim-league-toolkit')}</h4>
                    <SessionAttributeFields fields={fields} values={attributes} onChange={handleAttributeChange}/>
                </div>
            )}

            <div className="flex justify-content-end gap-2 mt-4">
                <Button
                    label={__('Cancel', 'sim-league-toolkit')}
                    severity="secondary"
                    onClick={onCancel}
                    disabled={loading}
                />
                <Button
                    label={__('Save', 'sim-league-toolkit')}
                    onClick={handleSubmit}
                    loading={loading}
                />
            </div>
        </div>
    );
};

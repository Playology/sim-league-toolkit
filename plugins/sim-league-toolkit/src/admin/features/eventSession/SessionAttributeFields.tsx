import {Calendar} from 'primereact/calendar';
import {Checkbox} from 'primereact/checkbox';
import {Dropdown} from 'primereact/dropdown';
import {InputNumber} from 'primereact/inputnumber';
import {InputText} from 'primereact/inputtext';

import {FieldDefinition} from '../../types/FieldDefinition';

interface SessionAttributeFieldsProps {
    fields: FieldDefinition[];
    values: Record<string, unknown>;
    onChange: (key: string, value: unknown) => void;
}

const parseTimeString = (timeStr: string): Date | null => {
    if (!timeStr) return null;
    const [hours, minutes] = timeStr.split(':').map(Number);
    const date = new Date();
    date.setHours(hours, minutes, 0, 0);
    return date;
};

const formatTimeDate = (date: Date | null): string => {
    if (!date) return '08:00';
    return `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
};

export const SessionAttributeFields = ({fields, values, onChange}: SessionAttributeFieldsProps) => {
    const renderField = (field: FieldDefinition) => {
        const value = values[field.key];

        switch (field.type) {
            case 'select':
                return (
                    <div key={field.key} className="field">
                        <label htmlFor={field.key}>{field.label}</label>
                        <Dropdown
                            id={field.key}
                            value={value}
                            options={field.options ?? []}
                            optionLabel="label"
                            optionValue="value"
                            onChange={(e) => onChange(field.key, e.value)}
                            className="w-full"
                        />
                    </div>
                );

            case 'boolean':
                return (
                    <div key={field.key} className="field-checkbox">
                        <Checkbox
                            id={field.key}
                            checked={value as boolean}
                            onChange={(e) => onChange(field.key, e.checked)}
                        />
                        <label htmlFor={field.key} className="ml-2">
                            {field.label}
                        </label>
                    </div>
                );

            case 'number':
                return (
                    <div key={field.key} className="field">
                        <label htmlFor={field.key}>{field.label}</label>
                        <InputNumber
                            id={field.key}
                            value={value as number}
                            onValueChange={(e) => onChange(field.key, e.value)}
                            min={field.validation?.min}
                            max={field.validation?.max}
                            className="w-full"
                        />
                    </div>
                );

            case 'time':
                return (
                    <div key={field.key} className="field">
                        <label htmlFor={field.key}>{field.label}</label>
                        <Calendar
                            id={field.key}
                            value={parseTimeString(value as string)}
                            onChange={(e) => onChange(field.key, formatTimeDate(e.value as Date))}
                            timeOnly
                            hourFormat="24"
                            className="w-full"
                        />
                    </div>
                );

            default:
                return (
                    <div key={field.key} className="field">
                        <label htmlFor={field.key}>{field.label}</label>
                        <InputText
                            id={field.key}
                            value={(value as string) ?? ''}
                            onChange={(e) => onChange(field.key, e.target.value)}
                            className="w-full"
                        />
                    </div>
                );
        }
    };

    return <>{fields.map(renderField)}</>;
};

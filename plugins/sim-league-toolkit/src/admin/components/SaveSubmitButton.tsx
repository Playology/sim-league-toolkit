import {__} from '@wordpress/i18n';

import {Button} from 'primereact/button';

interface SaveSubmitButtonProps {
    disabled?: boolean;
    name?: string;
}

export const SaveSubmitButton = ({disabled, name = 'submitForm'}: SaveSubmitButtonProps) => {
    return (
        <Button type='submit' disabled={disabled} name={name} style={{marginTop: '1rem'}}>
            {__('Save', 'sim-league-toolkit')}
        </Button>
    );
}
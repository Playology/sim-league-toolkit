import {__} from '@wordpress/i18n';

import {Button} from 'primereact/button';

interface CancelButtonProps {
    onCancel?: () => void;
    disabled?: boolean;
}

export const CancelButton = ({onCancel, disabled = false}: CancelButtonProps) => {
    return (
        <Button severity='secondary' onClick={onCancel} disabled={disabled}
                style={{marginTop: '1rem', marginLeft: '.5rem', marginRight: '.5rem'}}>
            {__('Cancel', 'sim-league-toolkit')}
        </Button>
    );
}
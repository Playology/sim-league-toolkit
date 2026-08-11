import {__, sprintf} from '@wordpress/i18n';
import {useState} from '@wordpress/element';

import {Button} from 'primereact/button';
import {Card} from 'primereact/card';
import {ConfirmDialog} from 'primereact/confirmdialog';
import {Message} from 'primereact/message';
import {Tag} from 'primereact/tag';

import {BusyIndicator} from '../../components/BusyIndicator';
import {
    homeItemKey,
    ProvisioningItemStatus,
    useProvisioningStatus,
    useProvisionItem,
    useRemoveProvisionedItem,
} from '../../../features/provisioning';

const stateLabel = (state: ProvisioningItemStatus['state']): string => {
    switch (state) {
        case 'notProvisioned':
            return __('Not provisioned', 'sim-league-toolkit');
        case 'conflictFound':
            return __('Existing item found', 'sim-league-toolkit');
        case 'provisioned':
            return __('Provisioned', 'sim-league-toolkit');
        case 'drifted':
            return __('Edited since provisioned', 'sim-league-toolkit');
    }
};

const stateSeverity = (state: ProvisioningItemStatus['state']): 'info' | 'warning' | 'success' => {
    switch (state) {
        case 'conflictFound':
            return 'warning';
        case 'drifted':
            return 'warning';
        case 'provisioned':
            return 'success';
        default:
            return 'info';
    }
};

type PendingConfirmation = { itemKey: string; kind: 'deleteDrifted' | 'overwrite' };

export const SiteSetup = () => {
    const {data: status, isLoading} = useProvisioningStatus();
    const provisionItem = useProvisionItem();
    const removeItem = useRemoveProvisionedItem();
    const [pendingConfirmation, setPendingConfirmation] = useState<PendingConfirmation | null>(null);

    const isBusy = provisionItem.isPending || removeItem.isPending;

    const onCreate = (itemKey: string) => provisionItem.mutate({itemKey, action: 'create'});
    const onAdopt = (itemKey: string) => provisionItem.mutate({itemKey, action: 'adopt'});
    const onStopTracking = (itemKey: string) => removeItem.mutate({itemKey, mode: 'stopTracking'});
    const onDeleteClean = (itemKey: string) => removeItem.mutate({itemKey, mode: 'delete'});

    const onDeleteDrifted = (itemKey: string) => setPendingConfirmation({itemKey, kind: 'deleteDrifted'});
    const onOverwrite = (itemKey: string) => setPendingConfirmation({itemKey, kind: 'overwrite'});
    const onCancelConfirmation = () => setPendingConfirmation(null);
    const onConfirm = () => {
        if (pendingConfirmation?.kind === 'deleteDrifted') {
            removeItem.mutate({itemKey: pendingConfirmation.itemKey, mode: 'delete', confirmDrift: true});
        } else if (pendingConfirmation?.kind === 'overwrite') {
            provisionItem.mutate({itemKey: pendingConfirmation.itemKey, action: 'overwrite'});
        }
        setPendingConfirmation(null);
    };

    const renderActions = (item: ProvisioningItemStatus) => {
        switch (item.state) {
            case 'notProvisioned':
                return (
                    <Button label={__('Create', 'sim-league-toolkit')} icon='pi pi-plus'
                            disabled={isBusy} onClick={() => onCreate(item.itemKey)}/>
                );
            case 'conflictFound':
                return (
                    <>
                        <Message severity='info' style={{marginBottom: '0.75rem', width: '100%'}}
                                 text={sprintf(
                                     __('Found an existing item: "%1$s" (last modified %2$s). Adopt it to start tracking it here without changing its content, overwrite it with SLTK\'s generated content (e.g. if it\'s just placeholder content left behind by another theme), or leave it alone — it will show up here again next time.', 'sim-league-toolkit'),
                                     item.existingTitle ?? '',
                                     item.existingModifiedAt ?? ''
                                 )}/>
                        <div style={{display: 'flex', gap: '0.5rem'}}>
                            <Button label={__('Adopt existing', 'sim-league-toolkit')} icon='pi pi-link'
                                    disabled={isBusy} onClick={() => onAdopt(item.itemKey)}/>
                            <Button label={__('Overwrite', 'sim-league-toolkit')} icon='pi pi-refresh'
                                    className='p-button-warning p-button-outlined' disabled={isBusy}
                                    onClick={() => onOverwrite(item.itemKey)}/>
                        </div>
                    </>
                );
            case 'provisioned':
                return (
                    <div style={{display: 'flex', gap: '0.5rem'}}>
                        {item.itemKey !== homeItemKey &&
                            <Button label={__('Refresh content', 'sim-league-toolkit')} icon='pi pi-refresh'
                                    className='p-button-outlined' disabled={isBusy}
                                    onClick={() => onOverwrite(item.itemKey)}/>}
                        <Button label={__('Stop tracking', 'sim-league-toolkit')} icon='pi pi-eye-slash'
                                className='p-button-outlined' disabled={isBusy}
                                onClick={() => onStopTracking(item.itemKey)}/>
                        <Button label={__('Delete', 'sim-league-toolkit')} icon='pi pi-trash'
                                className='p-button-danger p-button-outlined' disabled={isBusy}
                                onClick={() => onDeleteClean(item.itemKey)}/>
                    </div>
                );
            case 'drifted':
                return (
                    <>
                        <Message severity='warn' style={{marginBottom: '0.75rem', width: '100%'}}
                                 text={__('This item has been edited since it was provisioned or adopted.', 'sim-league-toolkit')}/>
                        <div style={{display: 'flex', gap: '0.5rem'}}>
                            {item.itemKey !== homeItemKey &&
                                <Button label={__('Refresh content', 'sim-league-toolkit')} icon='pi pi-refresh'
                                        className='p-button-outlined' disabled={isBusy}
                                        onClick={() => onOverwrite(item.itemKey)}/>}
                            <Button label={__('Stop tracking', 'sim-league-toolkit')} icon='pi pi-eye-slash'
                                    className='p-button-outlined' disabled={isBusy}
                                    onClick={() => onStopTracking(item.itemKey)}/>
                            <Button label={__('Delete', 'sim-league-toolkit')} icon='pi pi-trash'
                                    className='p-button-danger p-button-outlined' disabled={isBusy}
                                    onClick={() => onDeleteDrifted(item.itemKey)}/>
                        </div>
                    </>
                );
        }
    };

    return (
        <div style={{padding: '1rem'}}>
            <h1>{__('Site Setup', 'sim-league-toolkit')}</h1>
            <p>{__('Create typical starter pages and a navigation menu, already built from SLTK\'s blocks, that you can then edit and build on in whatever theme is currently active.', 'sim-league-toolkit')}</p>

            <BusyIndicator isBusy={isLoading || isBusy}/>

            {status?.warnings.map((warning, index) => (
                <Message key={index} severity='warn' text={warning}
                         style={{marginBottom: '0.75rem', width: '100%', maxWidth: '700px'}}/>
            ))}

            {status?.items.map((item) => (
                <Card key={item.itemKey} style={{margin: '1rem 0', maxWidth: '700px'}}
                      title={
                          <div style={{display: 'flex', alignItems: 'center', justifyContent: 'space-between'}}>
                              <span>{item.label}</span>
                              <Tag value={stateLabel(item.state)} severity={stateSeverity(item.state)}/>
                          </div>
                      }>
                    {renderActions(item)}
                </Card>
            ))}

            {pendingConfirmation &&
                <ConfirmDialog visible={!!pendingConfirmation} onHide={onCancelConfirmation} accept={onConfirm}
                               reject={onCancelConfirmation}
                               header={pendingConfirmation.kind === 'overwrite'
                                   ? __('Confirm Overwrite', 'sim-league-toolkit')
                                   : __('Confirm Delete', 'sim-league-toolkit')}
                               icon='pi pi-exclamation-triangle'
                               message={pendingConfirmation.kind === 'overwrite'
                                   ? __('This will replace the existing content with SLTK\'s generated content. Are you sure?', 'sim-league-toolkit')
                                   : __('This item has been edited since it was provisioned. Deleting it will permanently remove that content. Are you sure?', 'sim-league-toolkit')}
                               acceptLabel={pendingConfirmation.kind === 'overwrite'
                                   ? __('Yes, overwrite', 'sim-league-toolkit')
                                   : __('Yes, delete', 'sim-league-toolkit')}
                               rejectLabel={__('Cancel', 'sim-league-toolkit')}/>}
        </div>
    );
};

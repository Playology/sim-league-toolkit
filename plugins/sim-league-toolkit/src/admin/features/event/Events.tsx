import {__} from '@wordpress/i18n';
import {useState} from '@wordpress/element';

import {ConfirmDialog} from 'primereact/confirmdialog';
import {DataView} from 'primereact/dataview';
import {InputText} from 'primereact/inputtext';

import {BusyIndicator} from '../../components/BusyIndicator';
import {StandaloneEvent, useStandaloneEvents, useDeleteStandaloneEvent} from '../../../features/standaloneEvent';
import {StandaloneEventCard} from '../standaloneEvent/StandaloneEventCard';
import {NewStandaloneEventEditor} from '../standaloneEvent/NewStandaloneEventEditor';
import {StandaloneEventEditor} from '../standaloneEvent/StandaloneEventEditor';
import {useSearchAndSort} from '../../hooks/useSearchAndSort';

export const Events = () => {
    const {data: events = [], isLoading} = useStandaloneEvents();
    const {mutateAsync: deleteEvent} = useDeleteStandaloneEvent();
    const {searchTerm, setSearchTerm, items: visibleEvents} = useSearchAndSort(
        events, e => e.name, e => e.eventDate);

    const [isAdding, setIsAdding] = useState(false);
    const [isEditing, setIsEditing] = useState(false);
    const [selectedItem, setSelectedItem] = useState<StandaloneEvent>();
    const [showDeleteConfirmation, setShowDeleteConfirmation] = useState(false);

    const onAdd = () => {
        setIsAdding(true);
        setIsEditing(false);
    };

    const onCancelDelete = () => {
        setShowDeleteConfirmation(false);
        setSelectedItem(null);
    };

    const onConfirmDelete = async () => {
        setShowDeleteConfirmation(false);
        await deleteEvent(selectedItem.id);
        setSelectedItem(null);
    };

    const onDelete = (item: StandaloneEvent) => {
        setSelectedItem(item);
        setShowDeleteConfirmation(true);
    };

    const onEdit = (item: StandaloneEvent) => {
        setIsEditing(true);
        setIsAdding(false);
        setSelectedItem(item);
    };

    const onEditorCancelled = () => {
        setIsEditing(false);
        setIsAdding(false);
        setSelectedItem(null);
    };

    const onEditorSaved = () => {
        setIsEditing(false);
        setIsAdding(false);
        setSelectedItem(null);
    };

    const headerTemplate = () => {
        return (
            <div className='flex justify-content-between align-items-center'>
                <div>{__('Events', 'sim-league-toolkit')}</div>
                <div className='flex align-items-center gap-2'>
                    <InputText value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)}
                               placeholder={__('Search by name...', 'sim-league-toolkit')}/>
                    <button className='p-panel-header-icon p-link mr-2' onClick={onAdd}
                            title={__('Add a new Event', 'sim-league-toolkit')}>
                        <span className='pi pi-plus'></span>
                    </button>
                </div>
            </div>
        );
    };

    const itemTemplate = (item: StandaloneEvent) => {
        return <StandaloneEventCard standaloneEvent={item} key={item.id} onRequestEdit={onEdit}
                                    onRequestDelete={onDelete}/>;
    };

    return (
        <>
            {!isAdding && !isEditing && <>
                <BusyIndicator isBusy={isLoading}/>
                <h3>{__('Events', 'sim-league-toolkit')}</h3>
                <p>
                    {__('The standalone events you have created are displayed below.', 'sim-league-toolkit')}
                </p>
                <DataView value={visibleEvents} itemTemplate={itemTemplate} layout='grid' header={headerTemplate()}
                          emptyMessage={__('No Events have been defined.', 'sim-league-toolkit')}
                          style={{marginRight: '1rem'}}/>
            </>}
            {isAdding && <NewStandaloneEventEditor onSaved={onEditorSaved} onCancelled={onEditorCancelled}/>}
            {isEditing && (
                <StandaloneEventEditor onSaved={onEditorSaved} onCancelled={onEditorCancelled}
                                       standaloneEvent={selectedItem}/>
            )}
            {selectedItem && showDeleteConfirmation &&
                <ConfirmDialog visible={showDeleteConfirmation} onHide={onCancelDelete} accept={onConfirmDelete}
                               reject={onCancelDelete}
                               header={__('Confirm Delete', 'sim-league-toolkit')}
                               icon='pi pi-exclamation-triangle'
                               acceptLabel={__('Yes', 'sim-league-toolkit)')}
                               rejectLabel={__('No', 'sim-league-toolkit')}
                               message={__('Are you sure you want to delete', 'sim-league-toolkit') + ' ' + selectedItem.name + '?'}
                               style={{maxWidth: '50%'}}/>
            }
        </>
    );
};

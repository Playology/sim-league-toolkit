import {__} from '@wordpress/i18n';
import {useEffect, useState} from '@wordpress/element';

import {ConfirmDialog} from 'primereact/confirmdialog';
import {DataView} from 'primereact/dataview';
import {InputText} from 'primereact/inputtext';

import {BusyIndicator} from '../../components/BusyIndicator';
import {Championship, ChampionshipEvent, useChampionship, useChampionships, useDeleteChampionship} from '../../../features/championship';
import {ChampionshipBuilderWizard} from '../championshipBuilder/ChampionshipBuilderWizard';
import {ChampionshipCard} from './ChampionshipCard';
import {ChampionshipEditor} from './ChampionshipEditor';
import {ChampionshipEventEditor} from '../championshipEvent/ChampionshipEventEditor';
import {NewChampionshipEditor} from './NewChampionshipEditor';
import {useSearchAndSort} from '../../hooks/useSearchAndSort';

export const Championships = () => {
    const {data: championships = [], isLoading} = useChampionships();
    const {mutateAsync: deleteChampionship} = useDeleteChampionship();
    const {searchTerm, setSearchTerm, items: visibleChampionships} = useSearchAndSort(
        championships, c => c.name, c => c.startDate);

    const [isAdding, setIsAdding] = useState(false);
    const [isBuilding, setIsBuilding] = useState(false);
    const [isEditing, setIsEditing] = useState(false);
    const [selectedItem, setSelectedItem] = useState<Championship>();
    const [showDeleteConfirmation, setShowDeleteConfirmation] = useState(false);
    const [editingEvent, setEditingEvent] = useState<ChampionshipEvent | null>(null);
    const [returnToEventsTab, setReturnToEventsTab] = useState(false);
    const [builtChampionshipId, setBuiltChampionshipId] = useState<number | null>(null);

    const {data: builtChampionship} = useChampionship(builtChampionshipId ?? 0);

    useEffect(() => {
        if (!builtChampionship) {
            return;
        }

        setSelectedItem(builtChampionship);
        setIsEditing(true);
        setIsBuilding(false);
        setBuiltChampionshipId(null);
    }, [builtChampionship]);

    const onAdd = () => {
        setIsAdding(true);
        setIsBuilding(false);
        setIsEditing(false);
    };

    const onBuild = () => {
        setIsBuilding(true);
        setIsAdding(false);
        setIsEditing(false);
    };

    const onBuildSaved = (championshipId: number) => {
        setBuiltChampionshipId(championshipId);
    };

    const onCancelDelete = () => {
        setShowDeleteConfirmation(false);
        setSelectedItem(null);
    };

    const onConfirmDelete = async () => {
        setShowDeleteConfirmation(false);
        await deleteChampionship(selectedItem.id);
        setSelectedItem(null);
    };

    const onDelete = (item: Championship) => {
        setSelectedItem(item);
        setShowDeleteConfirmation(true);
    };

    const onEdit = (item: Championship) => {
        setIsEditing(true);
        setIsAdding(false);
        setSelectedItem(item);
    };

    const onEditorCancelled = () => {
        setIsEditing(false);
        setIsAdding(false);
        setIsBuilding(false);
        setSelectedItem(null);
        setEditingEvent(null);
        setReturnToEventsTab(false);
        setBuiltChampionshipId(null);
    };

    const onEditorSaved = () => {
        setIsEditing(false);
        setIsAdding(false);
        setIsBuilding(false);
        setSelectedItem(null);
        setEditingEvent(null);
        setReturnToEventsTab(false);
    };

    const onEditEvent = (event: ChampionshipEvent) => {
        setEditingEvent(event);
        setReturnToEventsTab(false);
    };

    const onEventEditorDone = () => {
        setEditingEvent(null);
        setReturnToEventsTab(true);
    };

    const headerTemplate = () => {
        return (
            <div className='flex justify-content-between align-items-center'>
                <div>{__('Championships', 'sim-league-toolkit')}</div>
                <div className='flex align-items-center gap-2'>
                    <InputText value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)}
                               placeholder={__('Search by name...', 'sim-league-toolkit')}/>
                    <button className='p-panel-header-icon p-link mr-2' onClick={onBuild}
                            title={__('Build a new Championship', 'sim-league-toolkit')}>
                        <span className='pi pi-flag-fill'></span>
                    </button>
                    <button className='p-panel-header-icon p-link mr-2' onClick={onAdd}
                            title={__('Add a new Championship', 'sim-league-toolkit')}>
                        <span className='pi pi-plus'></span>
                    </button>
                </div>
            </div>
        );
    };

    const itemTemplate = (item: Championship) => {
        return <ChampionshipCard championship={item} key={item.id} onRequestEdit={onEdit}
                                 onRequestDelete={onDelete}/>;
    };

    return (
        <>
            {!isAdding && !isBuilding && !isEditing && <>
                <BusyIndicator isBusy={isLoading}/>
                <h3>{__('Championships', 'sim-league-toolkit')}</h3>
                <p>
                    {__('The championships you have created are displayed below.', 'sim-league-toolkit')}
                </p>

                <DataView value={visibleChampionships} itemTemplate={itemTemplate} layout='grid'
                          header={headerTemplate()}
                          emptyMessage={__('No Championships have been defined.', 'sim-league-toolkit')}
                          style={{marginRight: '1rem'}}/>
            </>
            }
            {isAdding && <NewChampionshipEditor onSaved={onEditorSaved} onCancelled={onEditorCancelled}/>}
            {isBuilding && builtChampionshipId === null &&
                <ChampionshipBuilderWizard onSaved={onBuildSaved} onCancelled={onEditorCancelled}/>}
            {isBuilding && builtChampionshipId !== null && <BusyIndicator isBusy={true}/>}
            {isEditing && !editingEvent && (
                <ChampionshipEditor onSaved={onEditorSaved} onCancelled={onEditorCancelled}
                                    championship={selectedItem} onEditEvent={onEditEvent}
                                    initialActiveTabIndex={returnToEventsTab ? 2 : 0}/>
            )}
            {isEditing && editingEvent && (
                <ChampionshipEventEditor championshipEvent={editingEvent} gameId={selectedItem.gameId}
                                         championshipType={selectedItem.championshipType}
                                         trackMasterTrackId={selectedItem.trackMasterTrackId}
                                         trackMasterTrackLayoutId={selectedItem.trackMasterTrackLayoutId}
                                         onCancelled={onEventEditorDone}/>
            )}
            {selectedItem && showDeleteConfirmation &&
                <ConfirmDialog visible={showDeleteConfirmation} onHide={onCancelDelete} accept={onConfirmDelete}
                               reject={onCancelDelete}
                               header={__('Confirm Delete', 'sim-league-toolkit')}
                               icon='pi pi-exclamation-triangle'
                               acceptLabel={__('Yes', 'sim-league-toolkit)')}
                               rejectLabel={__('No', 'sim-league-toolkit')}
                               message={__('Deleting', 'sim-league-toolkit') + ' ' + selectedItem.name + ' ' + __(
                                   'will remove' +
                                   ' it including all of the events, results, standings and related data!!.  Do you wish to delete ',
                                   'sim-league-toolkit') + ' ' + selectedItem.name + '?'}
                               style={{maxWidth: '50%'}}/>
            }
        </>
    );
};
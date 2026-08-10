import {__} from '@wordpress/i18n';
import {useState} from '@wordpress/element';

import {ConfirmDialog} from 'primereact/confirmdialog';
import {DataView} from 'primereact/dataview';

import {BusyIndicator} from '../../components/BusyIndicator';
import {ChampionshipEvent, useChampionshipEvents, useDeleteChampionshipEvent} from '../../../features/championship';
import {ChampionshipEventCard} from './ChampionshipEventCard';
import {ChampionshipType} from '../../../enums/generated/ChampionshipType';
import {NewChampionshipEventEditor} from './NewChampionshipEventEditor';

interface ChampionshipEventsProps {
    championshipId: number,
    gameId: number,
    championshipType: ChampionshipType,
    trackMasterTrackId?: number,
    trackMasterTrackLayoutId?: number,
    onEditEvent: (event: ChampionshipEvent) => void,
}

export const ChampionshipEvents = ({
                                        championshipId,
                                        gameId,
                                        championshipType,
                                        trackMasterTrackId,
                                        trackMasterTrackLayoutId,
                                        onEditEvent
                                    }: ChampionshipEventsProps) => {

    const {data: championshipEvents = [], isLoading} = useChampionshipEvents(championshipId);
    const {mutateAsync: deleteChampionshipEvent} = useDeleteChampionshipEvent(championshipId);

    const [isAdding, setIsAdding] = useState(false);
    const [selectedItem, setSelectedItem] = useState<ChampionshipEvent>(null);
    const [showDeleteConfirmation, setShowDeleteConfirmation] = useState(false);

    const onAdd = () => {
        setIsAdding(true);
    };

    const onCancelAdd = () => {
        setIsAdding(false);
    };

    const onCancelDelete = () => {
        setShowDeleteConfirmation(false);
        setSelectedItem(null);
    };

    const onConfirmDelete = async () => {
        setShowDeleteConfirmation(false);
        await deleteChampionshipEvent(selectedItem.id);
        setSelectedItem(null);
    };

    const onDelete = (item: ChampionshipEvent) => {
        setSelectedItem(item);
        setShowDeleteConfirmation(true);
    };

    const onEdit = (item: ChampionshipEvent) => {
        onEditEvent(item);
    };

    const onNewSaved = () => {
        setIsAdding(false);
    };

    const headerTemplate = () => {
        return (
            <div className='flex justify-content-between'>
                <div>{__('Events', 'sim-league-toolkit')}</div>
                <div>
                    <button className='p-panel-header-icon p-link mr-2' onClick={onAdd}
                            title={__('Add a new Championship Event', 'sim-league-toolkit')}>
                        <span className='pi pi-plus'></span>
                    </button>
                </div>
            </div>
        );
    };

    const itemTemplate = (item: ChampionshipEvent) => {
        return <ChampionshipEventCard championshipEvent={item} key={item.id}
                                      onRequestEdit={onEdit}
                                      onRequestDelete={onDelete}/>;
    };

    return (
        <>

            <BusyIndicator isBusy={isLoading}/>
            <DataView value={championshipEvents} itemTemplate={itemTemplate} layout='grid' header={headerTemplate()}
                      emptyMessage={__('No events have been created for this championship.', 'sim-league-toolkit')}
                      style={{marginRight: '1rem'}}/>
            {isAdding &&
                <NewChampionshipEventEditor championshipId={championshipId} gameId={gameId}
                                            championshipType={championshipType}
                                            trackMasterTrackId={trackMasterTrackId}
                                            trackMasterTrackLayoutId={trackMasterTrackLayoutId}
                                            onSaved={onNewSaved}
                                            onCancelled={onCancelAdd}/>
            }
            {selectedItem && showDeleteConfirmation &&
                <ConfirmDialog visible={showDeleteConfirmation} onHide={onCancelDelete} accept={onConfirmDelete}
                               reject={onCancelDelete}
                               header={__('Confirm Delete', 'sim-league-toolkit')}
                               icon='pi pi-exclamation-triangle'
                               acceptLabel={__('Yes', 'sim-league-toolkit)')}
                               rejectLabel={__('No', 'sim-league-toolkit')}
                               message={__('Deleting', 'sim-league-toolkit') + ' ' + selectedItem.name + ' ' + __(
                                   'will remove it including all of the sessions, results, and related data!!.  Do you wish to delete ',
                                   'sim-league-toolkit') + ' ' + selectedItem.name + '?'}
                               style={{maxWidth: '50%'}}/>
            }
        </>
    );
};
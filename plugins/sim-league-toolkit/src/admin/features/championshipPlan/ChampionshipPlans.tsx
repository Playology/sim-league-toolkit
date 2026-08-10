import {__} from '@wordpress/i18n';
import {useState} from '@wordpress/element';

import {ConfirmDialog} from 'primereact/confirmdialog';
import {DataView} from 'primereact/dataview';
import {InputText} from 'primereact/inputtext';

import {BusyIndicator} from '../../components/BusyIndicator';
import {ChampionshipPlan, useChampionshipPlans, useDeleteChampionshipPlan} from '../../../features/championshipPlan';
import {ChampionshipPlanCard} from './ChampionshipPlanCard';
import {ChampionshipPlanEditor} from './ChampionshipPlanEditor';
import {NewChampionshipPlanEditor} from './NewChampionshipPlanEditor';
import {useSearchAndSort} from '../../hooks/useSearchAndSort';

export const ChampionshipPlans = () => {
    const {data: plans = [], isLoading} = useChampionshipPlans();
    const {mutateAsync: deletePlan} = useDeleteChampionshipPlan();
    const {searchTerm, setSearchTerm, items: visiblePlans} = useSearchAndSort(
        plans, p => p.name, p => p.startDate);

    const [isAdding, setIsAdding] = useState(false);
    const [isEditing, setIsEditing] = useState(false);
    const [selectedItem, setSelectedItem] = useState<ChampionshipPlan>();
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
        await deletePlan(selectedItem.id);
        setSelectedItem(null);
    };

    const onDelete = (item: ChampionshipPlan) => {
        setSelectedItem(item);
        setShowDeleteConfirmation(true);
    };

    const onEdit = (item: ChampionshipPlan) => {
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
                <div>{__('Championship Plans', 'sim-league-toolkit')}</div>
                <div className='flex align-items-center gap-2'>
                    <InputText value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)}
                               placeholder={__('Search by name...', 'sim-league-toolkit')}/>
                    <button className='p-panel-header-icon p-link mr-2' onClick={onAdd}
                            title={__('Add a new Championship Plan', 'sim-league-toolkit')}>
                        <span className='pi pi-plus'></span>
                    </button>
                </div>
            </div>
        );
    };

    const itemTemplate = (item: ChampionshipPlan) => {
        return <ChampionshipPlanCard plan={item} key={item.id} onRequestEdit={onEdit} onRequestDelete={onDelete}/>;
    };

    return (
        <>
            {!isAdding && !isEditing && <>
                <BusyIndicator isBusy={isLoading}/>
                <h3>{__('Championship Plans', 'sim-league-toolkit')}</h3>
                <p>
                    {__('Let members vote on candidate tracks, cars and classes before building a Championship from the results.', 'sim-league-toolkit')}
                </p>

                <DataView value={visiblePlans} itemTemplate={itemTemplate} layout='grid'
                          header={headerTemplate()}
                          emptyMessage={__('No Championship Plans have been defined.', 'sim-league-toolkit')}
                          style={{marginRight: '1rem'}}/>
            </>
            }
            {isAdding && <NewChampionshipPlanEditor onSaved={onEditorSaved} onCancelled={onEditorCancelled}/>}
            {isEditing && (
                <ChampionshipPlanEditor plan={selectedItem} onSaved={onEditorSaved} onCancelled={onEditorCancelled}/>
            )}
            {selectedItem && showDeleteConfirmation &&
                <ConfirmDialog visible={showDeleteConfirmation} onHide={onCancelDelete} accept={onConfirmDelete}
                               reject={onCancelDelete}
                               header={__('Confirm Delete', 'sim-league-toolkit')}
                               icon='pi pi-exclamation-triangle'
                               acceptLabel={__('Yes', 'sim-league-toolkit')}
                               rejectLabel={__('No', 'sim-league-toolkit')}
                               message={__('Deleting', 'sim-league-toolkit') + ' ' + selectedItem.name + ' ' + __(
                                   'will remove it including all votes and suggested classes. Do you wish to delete ',
                                   'sim-league-toolkit') + ' ' + selectedItem.name + '?'}
                               style={{maxWidth: '50%'}}/>
            }
        </>
    );
};

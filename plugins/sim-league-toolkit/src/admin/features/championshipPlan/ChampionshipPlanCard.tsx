import {__} from '@wordpress/i18n';

import {Button} from 'primereact/button';
import {Card} from 'primereact/card';

import {ChampionshipPlan} from '../../../features/championshipPlan';
import {ChampionshipType} from '../../../enums/generated/ChampionshipType';
import {PlanStatusLabels} from '../../../enums/generated/PlanStatus';

interface ChampionshipPlanCardProps {
    plan: ChampionshipPlan;
    onRequestEdit: (plan: ChampionshipPlan) => void;
    onRequestDelete: (plan: ChampionshipPlan) => void;
}

export const ChampionshipPlanCard = ({plan, onRequestEdit, onRequestDelete}: ChampionshipPlanCardProps) => {
    const startDate = new Date(plan.startDate).toLocaleDateString();

    const footer = (
        <>
            <Button label={__('Edit', 'sim-league-toolkit')} icon='pi pi-pencil'
                    onClick={() => onRequestEdit(plan)}/>
            <Button label={__('Delete', 'sim-league-toolkit')} icon='pi pi-times' severity='danger'
                    onClick={() => onRequestDelete(plan)} style={{marginLeft: '1rem'}}/>
        </>
    );

    return (
        <Card title={plan.name} subTitle={plan.game} footer={footer} style={{margin: '1rem', maxWidth: '400px'}}>
            <table className='table-no-border'>
                <tbody>
                <tr>
                    <th scope='row'>{__('Platform', 'sim-league-toolkit')}</th>
                    <td>{plan.platform}</td>
                </tr>
                <tr>
                    <th scope='row'>{__('Target Start Date', 'sim-league-toolkit')}</th>
                    <td>{startDate}</td>
                </tr>
                <tr>
                    <th scope='row'>{__('Type', 'sim-league-toolkit')}</th>
                    <td>{plan.championshipType === ChampionshipType.TRACK_MASTER ? __('Track Master', 'sim-league-toolkit') : __('Standard', 'sim-league-toolkit')}</td>
                </tr>
                <tr>
                    <th scope='row'>{__('Status', 'sim-league-toolkit')}</th>
                    <td>{PlanStatusLabels[plan.status]}</td>
                </tr>
                {plan.createdChampionshipId && (
                    <tr>
                        <th scope='row'>{__('Championship', 'sim-league-toolkit')}</th>
                        <td>#{plan.createdChampionshipId}</td>
                    </tr>
                )}
                </tbody>
            </table>
        </Card>
    );
};

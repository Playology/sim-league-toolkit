import {__} from '@wordpress/i18n';

import {Button} from 'primereact/button';
import {Card} from 'primereact/card';

import {ChampionshipEvent} from '../../../features/championship';

interface ChampionshipEventCardProps {
    championshipEvent: ChampionshipEvent,
    onRequestEdit: (championshipEvent: ChampionshipEvent) => void
    onRequestDelete: (championshipEvent: ChampionshipEvent) => void
}

export const ChampionshipEventCard = ({
                                          championshipEvent,
                                          onRequestEdit,
                                          onRequestDelete
                                      }: ChampionshipEventCardProps) => {
    let startDateTime: Date = new Date(championshipEvent.startDateTime);
    const startDate = startDateTime.toLocaleDateString();
    const startTime = startDateTime.toLocaleTimeString();

    const footer = (
        <>
            <Button label={__('Edit', 'sim-league-toolkit')} icon='pi pi-pencil'
                    onClick={() => onRequestEdit(championshipEvent)}/>
            {!championshipEvent.isCompleted && (
                <Button label={__('Delete', 'sim-league-toolkit')} icon='pi pi-times' severity='danger'
                        onClick={() => onRequestDelete(championshipEvent)} style={{marginLeft: '1rem'}}/>)
            }
        </>
    );

    const header = (
        <img alt={__('Banner image', 'sim-league-toolkit')}
             src={championshipEvent.bannerImageUrl}/>
    );
    
    return (
        <Card title={championshipEvent.name}
              footer={footer} header={header}
              style={{margin: '1rem', maxWidth: '400px'}}>
            <table className='table-no-border'>
                <tbody>
                <tr>
                    <th scope='row'>{__('Track', 'sim-league-toolkit')}</th>
                    <td>{championshipEvent.track}</td>
                </tr>
                {championshipEvent.trackLayout &&
                    <tr>
                        <th scope='row'>{__('Track Layout', 'sim-league-toolkit')}</th>
                        <td>{championshipEvent.trackLayout}</td>
                    </tr>
                }
                {championshipEvent.trackMasterCar &&
                    <tr>
                        <th scope='row'>{__('Car', 'sim-league-toolkit')}</th>
                        <td>{championshipEvent.trackMasterCar}</td>
                    </tr>
                }
                <tr>
                    <th scope='row'>{__('Start Date', 'sim-league-toolkit')}</th>
                    <td>{startDate}</td>
                </tr>
                <tr>
                    <th scope='row'>{__('Start Time', 'sim-league-toolkit')}</th>
                    <td>{startTime}</td>
                </tr>
                <tr>
                    <th scope='row'>{__('Completed', 'sim-league-toolkit')}</th>
                    <td>{championshipEvent.isCompleted ? __('Yes', 'sim-league-toolkit') : __('No',
                                                                                              'sim-league-toolkit')}</td>
                </tr>
                </tbody>
            </table>
        </Card>
    );
};
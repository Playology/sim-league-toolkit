import {Dashboard} from '../features/dashboard/Dashboard';
import {Games} from '../features/game/Games';
import {RuleSets} from '../features/ruleSet/RuleSets';
import {Championships} from '../features/championship/Championships';
import {ChampionshipPlans} from '../features/championshipPlan/ChampionshipPlans';
import {Events} from '../features/event/Events';
import {EventClasses} from '../features/eventClass/EventClasses';
import {ScoringSets} from '../features/scoringSet/ScoringSets';
import {Servers} from '../features/server/Servers';
import {ViewType} from '../types/ViewType';

interface ContentNavigatorProps {
    currentView:ViewType;
}
export const ContentNavigator = ({currentView}: ContentNavigatorProps) => {
    switch (currentView) {
        case 'championships':
            return <Championships/>;
        case 'championshipPlans':
            return <ChampionshipPlans/>;
        case 'events':
            return <Events/>;
        case 'eventClasses':
            return <EventClasses/>;
        case 'games':
            return <Games/>
        case 'ruleSets':
            return <RuleSets/>
        case 'scoringSets':
            return <ScoringSets/>;
        case 'servers':
            return <Servers/>;
        default:
            return <Dashboard/>
    }

}
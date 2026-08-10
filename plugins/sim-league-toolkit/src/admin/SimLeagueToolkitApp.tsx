import {__} from '@wordpress/i18n';
import {useState, useEffect} from 'react';
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import {ReactQueryDevtools} from '@tanstack/react-query-devtools';

import {Button} from 'primereact/button';
import {classNames} from 'primereact/utils';
import {Menu} from 'primereact/menu';
import {MenuItem, MenuItemOptions} from 'primereact/menuitem';
import {PrimeReactProvider} from 'primereact/api';
import {Sidebar} from 'primereact/sidebar';
import {Tooltip} from 'primereact/tooltip';

import {Notifications} from './components/Notifications';
import {ContentNavigator} from './components/ContentNavigator';
import {HeaderBar} from './components/HeaderBar';
import {useHashState} from './hooks/useHashState';
import {ViewType} from './types/ViewType';
import {ViewConfig} from './types/ViewConfig';

export const SimLeagueToolkitApp = () => {
    const stateKey: string = 'currentView';
    const [currentView, setCurrentView] = useHashState<ViewType>(stateKey, 'dashboard');
    const [isCollapsed, setIsCollapsed] = useState(false);
    const [isMobile, setIsMobile] = useState(false);
    const [sidebarVisible, setSidebarVisible] = useState(false);

    useEffect(() => {
        const handleResize = () => {
            const width = window.innerWidth;
            setIsMobile(width < 782);
            setIsCollapsed(width < 960);
        };

        handleResize();
        window.addEventListener('resize', handleResize);
        return () => window.removeEventListener('resize', handleResize);
    }, []);

    const viewConfigs: ViewConfig[] = [
        {
            label: __('Dashboard', 'sim-league-toolkit'),
            icon: 'fa-solid fa-gauge-high',
            view: 'dashboard'
        },
        {
            label: __('Championships', 'sim-league-toolkit'),
            icon: 'fa-solid fa-trophy',
            view: 'championships',
        },
        {
            label: __('Championship Plans', 'sim-league-toolkit'),
            icon: 'fa-solid fa-square-poll-vertical',
            view: 'championshipPlans',
        },
        {
            label: __('Standalone Events', 'sim-league-toolkit'),
            icon: 'fa-solid fa-flag-checkered',
            view: 'events',
        },
        {
            label: __('Event Classes', 'sim-league-toolkit'),
            icon: 'fa-solid fa-layer-group',
            view: 'eventClasses',
        },
        {
            label: __('Games', 'sim-league-toolkit'),
            icon: 'fa-solid fa-gamepad',
            view: 'games',
        },
        {
            label: __('Rule Sets', 'sim-league-toolkit'),
            icon: 'fa-solid fa-scale-balanced',
            view: 'ruleSets',
        },
        {
            label: __('Scoring Sets', 'sim-league-toolkit'),
            icon: 'fa-solid fa-table-list',
            view: 'scoringSets',
        },
        {
            label: __('Servers', 'sim-league-toolkit'),
            icon: 'fa-solid fa-server',
            view: 'servers',
        }
    ];

    const menuItems = viewConfigs.map(({label, icon, view}) => ({
        label,
        icon,
        template: (item: MenuItem, options: MenuItemOptions) => (
            <a
                className={options.className}
                onClick={options.onClick}
                data-pr-tooltip={isCollapsed ? label : undefined}
                data-pr-position='right'
            >
                <span className={`${icon} ${options.iconClassName}`}/>
                <span className={options.labelClassName}>{label}</span>
            </a>
        ),
        command: () => {
            setCurrentView(view);
            if (isMobile) {
                setSidebarVisible(false);
            }
        },
    }));

    const queryClient = new QueryClient({
                                            defaultOptions: {
                                                queries: {
                                                    staleTime: 5 * 60 * 1000,     // Data considered fresh for 5 min
                                                    gcTime: 10 * 60 * 1000,        // Garbage collected after 10 min
                                                    refetchOnWindowFocus: true,     // Refetch when user returns to tab
                                                    retry: 1,                       // Retry failed requests once
                                                },
                                            },
                                        });
    // WP admin's own chrome sits as high as z-index 99999 (#wpadminbar), above PrimeReact's
    // defaults ({modal: 1100, overlay: 1000, menu: 1000, tooltip: 1100, toast: 1200}) - shifting
    // every category up by the same amount keeps their relative stacking order (so an overlay like
    // a Dropdown still layers correctly above a Dialog that contains it) while clearing WP's chrome.
    const primeReactZIndex = {modal: 100100, overlay: 100000, menu: 100000, tooltip: 100100, toast: 100200};

    return (
        <QueryClientProvider client={queryClient}>
            <PrimeReactProvider value={{zIndex: primeReactZIndex}}>
                <Notifications/>
                <HeaderBar
                    startContent={
                        isMobile && (
                            <Button
                                icon='fa-solid fa-bars'
                                onClick={() => setSidebarVisible(true)}
                                className='p-button-text mobile-menu-toggle'
                                aria-label='Toggle menu'
                            />
                        )
                    }
                />
                <div className='main-container'>
                    <Tooltip target='[data-pr-tooltip]' showDelay={150}/>

                    {/* Desktop/Tablet Menu */}
                    {!isMobile && (
                        <div className={classNames('menu-container', {'collapsed': isCollapsed})}>
                            <Menu model={menuItems}/>
                        </div>
                    )}

                    {/* Mobile Sidebar */}
                    {isMobile && (
                        <Sidebar
                            visible={sidebarVisible}
                            onHide={() => setSidebarVisible(false)}
                            className='mobile-sidebar'
                            position='left'
                        >
                            <Menu model={menuItems}/>
                        </Sidebar>
                    )}

                    <div className='content-container'>
                        <ContentNavigator currentView={currentView}/>
                    </div>
                </div>
            </PrimeReactProvider>
            <ReactQueryDevtools initialIsOpen={false}/>
        </QueryClientProvider>
    );
};
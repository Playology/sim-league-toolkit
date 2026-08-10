import {useMemo, useState} from '@wordpress/element';

export const useSearchAndSort = <T, >(items: T[], getName: (item: T) => string, getDate: (item: T) => string | Date) => {
    const [searchTerm, setSearchTerm] = useState('');

    const results = useMemo(() => {
        const term = searchTerm.trim().toLowerCase();
        const filtered = term.length === 0
            ? items
            : items.filter(item => getName(item).toLowerCase().includes(term));

        return [...filtered].sort((a, b) => new Date(getDate(b)).getTime() - new Date(getDate(a)).getTime());
    }, [items, searchTerm]);

    return {searchTerm, setSearchTerm, items: results};
};

import { describe, it, expect } from 'vitest';

/**
 * These are unit tests for the filtering logic used in the IconifyFieldtype.
 * Extracted as pure functions for testability.
 */

/**
 * Check if a prefix matches a list of allowed prefixes.
 * Supports partial prefix matching (e.g. "mdi-" matches "mdi-light").
 */
function matchesAllowedPrefixes(prefix, allowedPrefixes) {
    return allowedPrefixes.some(allowed => {
        if (allowed === prefix) return true;
        if (allowed.endsWith('-') && prefix.startsWith(allowed)) return true;
        if (prefix.endsWith('-') && allowed.startsWith(prefix)) return true;
        return false;
    });
}

/**
 * Compute effective prefixes by merging global config with field config.
 * Field config narrows within global config bounds.
 * fieldPrefixes is now an array (from multi-select).
 */
function computeEffectivePrefixes(globalPrefixes, fieldPrefixes) {
    const resolved = Array.isArray(fieldPrefixes) ? fieldPrefixes : [];

    if (!resolved.length) return globalPrefixes;
    if (!globalPrefixes.length) return resolved;

    return resolved.filter(fp => matchesAllowedPrefixes(fp, globalPrefixes));
}

/**
 * Compute effective category from global config + field config.
 */
function computeEffectiveCategory(globalCategories, fieldCategory) {
    if (fieldCategory) {
        if (globalCategories.length && !globalCategories.includes(fieldCategory)) {
            return null;
        }
        return fieldCategory;
    }
    if (globalCategories.length === 1) return globalCategories[0];
    return null;
}

/**
 * Build search URL with filters applied.
 */
function buildSearchUrl(query, prefixes, category) {
    const url = new URL('https://api.iconify.design/search');
    url.searchParams.set('limit', '999');
    url.searchParams.set('query', query);

    if (prefixes.length) {
        url.searchParams.set('prefixes', prefixes.join(','));
    }

    if (category) {
        url.searchParams.set('category', category);
    }

    return url.toString();
}

// --- matchesAllowedPrefixes ---

describe('matchesAllowedPrefixes', () => {
    it('matches exact prefix', () => {
        expect(matchesAllowedPrefixes('mdi', ['mdi', 'tabler'])).toBe(true);
    });

    it('rejects non-matching prefix', () => {
        expect(matchesAllowedPrefixes('ph', ['mdi', 'tabler'])).toBe(false);
    });

    it('matches partial prefix with dash suffix', () => {
        expect(matchesAllowedPrefixes('mdi-light', ['mdi-'])).toBe(true);
    });

    it('does not match base prefix with partial pattern', () => {
        expect(matchesAllowedPrefixes('mdi', ['mdi-'])).toBe(false);
    });

    it('handles empty allowed list', () => {
        expect(matchesAllowedPrefixes('mdi', [])).toBe(false);
    });

    it('matches reverse partial (field has dash, global is exact)', () => {
        expect(matchesAllowedPrefixes('mdi-', ['mdi-light'])).toBe(true);
    });
});

// --- computeEffectivePrefixes ---

describe('computeEffectivePrefixes', () => {
    it('returns global prefixes when no field config', () => {
        expect(computeEffectivePrefixes(['mdi', 'tabler'], [])).toEqual(['mdi', 'tabler']);
    });

    it('returns field prefixes when no global config', () => {
        expect(computeEffectivePrefixes([], ['mdi', 'ph'])).toEqual(['mdi', 'ph']);
    });

    it('returns empty when both are empty', () => {
        expect(computeEffectivePrefixes([], [])).toEqual([]);
    });

    it('narrows field prefixes within global bounds', () => {
        expect(computeEffectivePrefixes(['mdi', 'tabler', 'ph'], ['mdi', 'ph'])).toEqual(['mdi', 'ph']);
    });

    it('excludes field prefixes not in global config', () => {
        expect(computeEffectivePrefixes(['mdi', 'tabler'], ['mdi', 'heroicons'])).toEqual(['mdi']);
    });

    it('returns empty when field prefixes are all outside global', () => {
        expect(computeEffectivePrefixes(['mdi'], ['tabler', 'ph'])).toEqual([]);
    });

    it('handles non-array field config gracefully', () => {
        expect(computeEffectivePrefixes(['mdi'], null)).toEqual(['mdi']);
        expect(computeEffectivePrefixes(['mdi'], undefined)).toEqual(['mdi']);
    });

    it('supports partial prefix matching in narrowing', () => {
        expect(computeEffectivePrefixes(['mdi-'], ['mdi-light'])).toEqual(['mdi-light']);
    });
});

// --- computeEffectiveCategory ---

describe('computeEffectiveCategory', () => {
    it('returns null when no config set', () => {
        expect(computeEffectiveCategory([], '')).toBeNull();
    });

    it('returns field category when set', () => {
        expect(computeEffectiveCategory([], 'UI 24px')).toBe('UI 24px');
    });

    it('returns field category when in global allowed list', () => {
        expect(computeEffectiveCategory(['UI 24px', 'General'], 'UI 24px')).toBe('UI 24px');
    });

    it('returns null when field category not in global allowed list', () => {
        expect(computeEffectiveCategory(['UI 24px'], 'Emoji')).toBeNull();
    });

    it('returns single global category as default', () => {
        expect(computeEffectiveCategory(['UI 24px'], '')).toBe('UI 24px');
    });

    it('returns null when multiple global categories and no field selection', () => {
        expect(computeEffectiveCategory(['UI 24px', 'General'], '')).toBeNull();
    });
});

// --- buildSearchUrl ---

describe('buildSearchUrl', () => {
    it('builds basic search URL', () => {
        const url = buildSearchUrl('home', [], null);
        expect(url).toContain('query=home');
        expect(url).toContain('limit=999');
        expect(url).not.toContain('prefixes');
        expect(url).not.toContain('category');
    });

    it('includes prefixes param', () => {
        const url = buildSearchUrl('home', ['mdi', 'tabler'], null);
        expect(url).toContain('prefixes=mdi%2Ctabler');
    });

    it('includes category param', () => {
        const url = buildSearchUrl('home', [], 'UI 24px');
        expect(url).toContain('category=UI+24px');
    });

    it('includes both prefixes and category', () => {
        const url = buildSearchUrl('home', ['mdi'], 'General');
        expect(url).toContain('prefixes=mdi');
        expect(url).toContain('category=General');
    });
});

// --- icons computed mapping ---

describe('icons mapping', () => {
    const mockResult = {
        icons: ['mdi:home', 'mdi:home-outline', 'tabler:home'],
        total: 3,
        limit: 999,
        start: 0,
        collections: {
            'mdi': {
                name: 'Material Design Icons',
                total: 7134,
                license: { title: 'Apache 2.0' },
                category: 'General',
            },
            'tabler': {
                name: 'Tabler Icons',
                total: 2925,
                license: { title: 'MIT' },
                category: 'UI 24px',
            },
        },
    };

    function mapIcons(result) {
        if (!result) return [];
        return result.icons.map(icon => ({
            name: icon,
            collection: result.collections[icon.split(':')[0]],
        }));
    }

    it('maps icons with collection data', () => {
        const icons = mapIcons(mockResult);
        expect(icons).toHaveLength(3);
        expect(icons[0].name).toBe('mdi:home');
        expect(icons[0].collection.name).toBe('Material Design Icons');
        expect(icons[2].collection.name).toBe('Tabler Icons');
    });

    it('returns empty array for null result', () => {
        expect(mapIcons(null)).toEqual([]);
    });

    it('filters icons by license client-side', () => {
        const resultWithLicenses = {
            icons: ['mdi:home', 'tabler:home', 'fa6-solid:house'],
            total: 3,
            limit: 999,
            start: 0,
            collections: {
                'mdi': { name: 'MDI', total: 7134, license: { title: 'Apache 2.0' }, category: 'General' },
                'tabler': { name: 'Tabler', total: 2925, license: { title: 'MIT' }, category: 'UI 24px' },
                'fa6-solid': { name: 'FA Solid', total: 1388, license: { title: 'CC BY 4.0' }, category: 'General' },
            },
        };

        function filterByLicenses(result, licenses) {
            if (!result) return [];
            return result.icons
                .map(icon => ({
                    name: icon,
                    collection: result.collections[icon.split(':')[0]],
                }))
                .filter(icon => {
                    if (!licenses.length) return true;
                    return icon.collection && licenses.includes(icon.collection.license?.title);
                });
        }

        // Filter to MIT only
        const mitOnly = filterByLicenses(resultWithLicenses, ['MIT']);
        expect(mitOnly).toHaveLength(1);
        expect(mitOnly[0].name).toBe('tabler:home');

        // Filter to MIT + Apache
        const mitAndApache = filterByLicenses(resultWithLicenses, ['MIT', 'Apache 2.0']);
        expect(mitAndApache).toHaveLength(2);
        expect(mitAndApache.map(i => i.name)).toEqual(['mdi:home', 'tabler:home']);

        // No filter = all
        const all = filterByLicenses(resultWithLicenses, []);
        expect(all).toHaveLength(3);
    });
});

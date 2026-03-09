<template>

    <div>
        <div v-if="value" class="flex items-center gap-4">
            <Dropdown>
                <template #trigger>
                    <iconify-icon v-if="typeof value === 'string'" :icon="value" class="cursor-pointer text-4xl" v-tooltip="{content: value, delay: 500, autoHide: false}"></iconify-icon>

                    <svg v-else v-bind="value.attributes" class="cursor-pointer text-4xl" v-html="value.body" v-tooltip="{content: value.name, delay: 500, autoHide: false}"></svg>
                </template>
                <DropdownMenu>
                    <DropdownItem text="Change" @click="openSearchModal" />
                    <DropdownItem text="Remove" variant="destructive" @click="update(null)" />
                </DropdownMenu>
            </Dropdown>
        </div>

        <Button v-else @click="openSearchModal">Browse Iconify</Button>

        <Stack
            v-model:open="searchModalIsOpen"
            title="Search and select an icon"
            icon="magnifying-glass"
        >

            <StackContent class="overflow-y-hidden!">

                <div class="h-full flex-1 flex flex-col">
                    <div class="w-full flex gap-4 mb-4">

                        <Input
                            ref="queryRef"
                            v-model="query"
                            icon="magnifying-glass"
                            class="flex-1"
                            placeholder="Search for an icon..."
                            @keydown.enter="search"
                        />

                        <Button variant="primary" @click="search" :disabled="loading">
                            {{ loading ? 'Searching...' : 'Search' }}
                        </Button>

                        <ToggleGroup v-model="listType">
                            <ToggleItem icon="layout-grid" value="grid" />
                            <ToggleItem icon="layout-list" value="table" />
                        </ToggleGroup>
                    </div>

                    <div v-if="activeFilterGroups.length" class="flex flex-wrap gap-1.5 mb-4">
                        <Badge
                            v-for="group in activeFilterGroups"
                            :key="group.label"
                            :text="group.label"
                            :color="group.color"
                            size="sm"
                            v-tooltip="group.tooltip"
                        />
                    </div>

                    <div v-if="result" class="overflow-y-scroll flex-1 pr-6">

                        <div v-if="icons.length === 0" class="text-center py-12 text-gray-500">
                            No icons found. Try a different search term.
                        </div>

                        <table v-if="listType === 'table' && icons.length" class="data-table w-full">
                            <thead>
                                <tr>
                                    <th>Icon</th>
                                    <th>Name</th>
                                    <th>Collection</th>
                                    <th>License</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="icon in icons" :key="icon.name">
                                    <td>
                                        <iconify-icon :icon="icon.name" class="text-2xl"></iconify-icon>
                                    </td>
                                    <td>
                                        <span v-text="icon.name" class="text-sm"></span>
                                    </td>
                                    <td>
                                        <span v-text="icon.collection.name" class="text-sm"></span>
                                    </td>
                                    <td>
                                        <span v-text="icon.collection.license.title" class="text-sm"></span>
                                    </td>
                                    <td>
                                        <Button size="sm" @click="select(icon)">Select</Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <Panel v-if="listType === 'grid' && icons.length" class="grid grid-cols-8 gap-2">

                            <button v-for="icon in icons" :key="icon.name" class="bg-white dark:bg-gray-800 rounded-xl ring ring-gray-200 dark:ring-gray-700 relative aspect-square flex items-center justify-center group cursor-pointer overflow-hidden" @click="select(icon)">

                                <iconify-icon :icon="icon.name" class="text-4xl text-gray-800 dark:text-white group-hover:scale-125 transition-all"></iconify-icon>

                                <div class="absolute bottom-0 left-0 right-0 p-2 bg-gray-50 dark:bg-gray-900 rounded-b-xl opacity-0 group-hover:opacity-100 transition-opacity my-0">
                                    <Badge :text="icon.name" size="sm" />
                                </div>

                                <div class="absolute top-1.5 left-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <Badge :text="icon.collection.license.title" size="sm" color="blue" />
                                </div>

                            </button>

                        </Panel>
                    </div>
                </div>
            </StackContent>
        </Stack>
    </div>
</template>

<script setup>
import { ref, computed, getCurrentInstance, watch, nextTick, onMounted } from 'vue';
import { getIcon, buildIcon } from 'iconify-icon';
import { FieldtypeMixin as Fieldtype } from '@statamic/cms';
import {
    Badge,
    Button,
    Input,
    Dropdown,
    DropdownMenu,
    DropdownItem,
    ToggleGroup,
    ToggleItem,
    Panel,
    Stack,
    StackContent,
    StackClose,
} from '@statamic/cms/ui';

defineOptions({ mixins: [Fieldtype] });

const searchModalIsOpen = ref(false);
const listType = ref('grid');
const query = ref('');
const result = ref(null);
const loading = ref(false);
const queryRef = ref(null);
const globalConfig = ref(null);

const { proxy } = getCurrentInstance();

/**
 * Build the effective prefixes list by merging global config with field config.
 * Field config can only narrow within what global config allows.
 */
const effectivePrefixes = computed(() => {
    const globalPrefixes = globalConfig.value?.allowed_prefixes ?? [];
    const fieldPrefixes = Array.isArray(proxy?.config?.prefixes) ? proxy.config.prefixes : [];

    // No field config: use global
    if (!fieldPrefixes.length) return globalPrefixes;

    // No global config: use field
    if (!globalPrefixes.length) return fieldPrefixes;

    // Both set: field narrows global (only keep field prefixes that are allowed globally)
    return fieldPrefixes.filter(fp => matchesAllowedPrefixes(fp, globalPrefixes));
});

/**
 * Build the effective category from global config + field config.
 */
const effectiveCategory = computed(() => {
    const globalCategories = globalConfig.value?.allowed_categories ?? [];
    const fieldCategory = (proxy?.config?.category ?? '');

    if (fieldCategory) {
        if (globalCategories.length && !globalCategories.includes(fieldCategory)) {
            return null;
        }
        return fieldCategory;
    }

    if (globalCategories.length === 1) return globalCategories[0];

    return null;
});

/**
 * Get the selected licenses from field config.
 */
const effectiveLicenses = computed(() => {
    return Array.isArray(proxy?.config?.licenses) ? proxy.config.licenses : [];
});

/**
 * Build grouped filter badges with truncation.
 * Each group shows up to 2 items, then "+ N more" with a tooltip listing all.
 */
const activeFilterGroups = computed(() => {
    const groups = [];
    const maxVisible = 2;

    if (effectivePrefixes.value.length) {
        const items = effectivePrefixes.value;
        const visible = items.slice(0, maxVisible).join(', ');
        const extra = items.length > maxVisible ? items.length - maxVisible : 0;
        groups.push({
            label: extra ? `Sets: ${visible} +${extra} more` : `Sets: ${visible}`,
            tooltip: items.length > maxVisible ? items.join(', ') : null,
            color: 'purple',
        });
    }

    if (effectiveCategory.value) {
        groups.push({
            label: `Category: ${effectiveCategory.value}`,
            tooltip: null,
            color: 'blue',
        });
    }

    if (effectiveLicenses.value.length) {
        const items = effectiveLicenses.value;
        const visible = items.slice(0, maxVisible).join(', ');
        const extra = items.length > maxVisible ? items.length - maxVisible : 0;
        groups.push({
            label: extra ? `Licenses: ${visible} +${extra} more` : `Licenses: ${visible}`,
            tooltip: items.length > maxVisible ? items.join(', ') : null,
            color: 'emerald',
        });
    }

    return groups;
});

const icons = computed(() => {
    if (!result.value) return [];

    const licenses = effectiveLicenses.value;

    return result.value.icons
        .map(icon => ({
            name: icon,
            collection: result.value.collections[icon.split(':')[0]],
        }))
        .filter(icon => {
            if (!licenses.length) return true;
            return icon.collection && licenses.includes(icon.collection.license?.title);
        });
});

watch(searchModalIsOpen, async (open) => {
    if (!open) return;
    await nextTick();
    setTimeout(() => {
        const el = queryRef.value?.$el?.querySelector('input');
        if (el) el.focus();
    }, 50);
});

onMounted(() => {
    fetchGlobalConfig();
});

function fetchGlobalConfig() {
    fetch(Statamic.$config.get('cpUrl') + '/iconify/config', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    })
        .then(res => res.ok ? res.json() : null)
        .then(data => {
            if (data) globalConfig.value = data;
        })
        .catch(() => {
            // Silently fail — will use no restrictions
        });
}

function openSearchModal() {
    searchModalIsOpen.value = true;
}

function search() {
    loading.value = true;
    const url = new URL('https://api.iconify.design/search');
    url.searchParams.set('limit', '999');
    url.searchParams.set('query', query.value);

    // Apply prefix filtering
    const prefixes = effectivePrefixes.value;
    if (prefixes.length) {
        url.searchParams.set('prefixes', prefixes.join(','));
    }

    // Apply category filtering
    const category = effectiveCategory.value;
    if (category) {
        url.searchParams.set('category', category);
    }

    fetch(url, {
        headers: {
            Accept: 'application/json',
        },
    })
        .then(async (res) => {
            if (!res.ok) throw new Error(`Iconify search failed: ${res.status}`);
            return res.json();
        })
        .then(data => {
            result.value = data;
        })
        .catch(() => {
            result.value = null;
        })
        .finally(() => {
            loading.value = false;
        });
}

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

function getIconBuildData(icon) {
    const iconBuildData = buildIcon(getIcon(icon.name));
    return {
        name: icon.name,
        ...iconBuildData,
    };
}

function select(icon) {
    if (proxy.config.store_as === 'name') {
        proxy.update(icon.name);
    } else if (proxy.config.store_as === 'svg_data') {
        proxy.update(getIconBuildData(icon));
    }
    searchModalIsOpen.value = false;
}
</script>

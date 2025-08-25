<template>

    <div>
        <div v-if="value" class="iconify:flex iconify:items-center iconify:gap-4">
            <Dropdown>
                <template #trigger>
                    <iconify-icon v-if="typeof value === 'string'" :icon="value" class="iconify:cursor-pointer iconify:text-4xl" v-tooltip="{content: value, delay: 500, autoHide: false}"></iconify-icon>

                    <svg v-else v-bind="value.attributes" class="iconify:cursor-pointer iconify:text-4xl" v-html="value.body" v-tooltip="{content: value.name, delay: 500, autoHide: false}"></svg>
                </template>
                <DropdownMenu>
                    <DropdownItem text="Change" @click="openSearchModal" />
                    <DropdownItem text="Remove" variant="destructive" @click="update(null)" />
                </DropdownMenu>
            </Dropdown>
        </div>

        <Button v-else @click="openSearchModal">Browse Iconify</Button>

        <stack
            v-if="searchModalIsOpen"
            @closed="searchModalIsOpen = false"
        >
            <div class="iconify:flex iconify:flex-col iconify:h-full bg-white dark:bg-gray-800 p-3 rounded-l-xl">

                <header class="flex items-center justify-between pl-3">
                    <ui-heading :text="__('Search and select an icon')" size="lg" icon="cog" />
                    <ui-button type="button" icon="x" variant="subtle" @click="searchModalIsOpen = false" />
                </header>

                <div class="iconify:px-3 iconify:md:px-8 iconify:py-4 iconify:pr-0 iconify:flex-1 iconify:flex iconify:flex-col iconify:overflow-hidden">
                    <div class="iconify:w-full iconify:flex iconify:gap-4 iconify:mb-4">

                        <Input ref="queryRef" v-model="query" icon="magnifying-glass" class="iconify:flex-1" placeholder="Search for an icon..." @keydown.enter="search" />

                        <Button variant="primary" @click="search" :disabled="loading">{{ loading ? 'Searching...' : 'Search' }}</Button>

                        <ToggleGroup v-model="listType">
                            <ToggleItem icon="layout-grid" value="grid" />
                            <ToggleItem icon="layout-list" value="table" />
                        </ToggleGroup>
                    </div>

                    <div v-if="result" class="iconify:overflow-y-scroll iconify:flex-1 iconify:pr-6">

                        <table v-if="listType === 'table'" class="data-table iconify:w-full">
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
                                        <iconify-icon :icon="icon.name" class="iconify:text-2xl"></iconify-icon>
                                    </td>
                                    <td>
                                        <span v-text="icon.name" class="iconify:text-sm"></span>
                                    </td>
                                    <td>
                                        <span v-text="icon.collection.name" class="iconify:text-sm"></span>
                                    </td>
                                    <td>
                                        <span v-text="icon.collection.license.title" class="iconify:text-sm"></span>
                                    </td>
                                    <td>
                                        <Button size="sm" @click="select(icon)">Select</Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <Panel v-if="listType === 'grid'" class="iconify:grid iconify:grid-cols-8 iconify:gap-2">

                            <button v-for="icon in icons" :key="icon.name" class="bg-white dark:bg-gray-800 rounded-xl ring ring-gray-200 dark:ring-x-0 dark:ring-b-0 dark:ring-gray-700 iconify:relative iconify:aspect-square iconify:flex iconify:items-center iconify:justify-center iconify:group iconify:cursor-pointer" @click="select(icon)">

                                <iconify-icon :icon="icon.name" class="iconify:text-4xl iconify:text-gray-800 iconify:dark:text-white iconify:group-hover:scale-125 iconify:transition-all"></iconify-icon>

                                <div class="iconify:absolute iconify:bottom-0 iconify:left-0 iconify:right-0 iconify:p-2 iconify:bg-gray-50 iconify:text-xs iconify:text-gray-500 iconify:text-center iconify:rounded-b-lg iconify:opacity-0 iconify:group-hover:opacity-100 iconify:transition-opacity iconify:my-0">
                                    <span v-text="icon.name"></span>
                                </div>

                                <div class="iconify:absolute iconify:top-2 iconify:left-2 iconify:p-1 iconify:bg-gray-200 iconify:text-xs iconify:text-gray-600 iconify:rounded-sm iconify:opacity-0 iconify:group-hover:opacity-100 iconify:transition-opacity">
                                    <span v-text="icon.collection.license.title"></span>
                                </div>

                            </button>

                        </Panel>
                    </div>

                </div>
                
                

            </div>
        </stack>

    </div>

</template>

<script setup>
import { ref, computed, getCurrentInstance } from 'vue';
import { getIcon, buildIcon } from 'iconify-icon';
import { FieldtypeMixin as Fieldtype } from '@statamic/cms';
import { Button, ButtonGroup, Input, Dropdown, DropdownMenu, DropdownItem, ToggleGroup, ToggleItem, Panel, Card } from '@statamic/cms/ui';

defineOptions({ mixins: [Fieldtype] });

const searchModalIsOpen = ref(false);
const listType = ref('grid');
const query = ref('');
const result = ref(null);
const loading = ref(false);
const queryRef = ref(null);

const icons = computed(() => {
    if (!result.value) return [];
    return result.value.icons.map(icon => ({
        name: icon,
        collection: result.value.collections[icon.split(':')[0]],
    }));
});

const { proxy } = getCurrentInstance();

function openSearchModal() {
    searchModalIsOpen.value = true;
    setTimeout(() => {
        const el = queryRef.value?.$el?.querySelector('input');
        if (el) el.focus();
    }, 300);
}

function search() {
    loading.value = true;
    ky.get('https://api.iconify.design/search?limit=999&query=' + query.value)
        .json()
        .then(data => {
            result.value = data;
        })
        .finally(() => {
            loading.value = false;
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
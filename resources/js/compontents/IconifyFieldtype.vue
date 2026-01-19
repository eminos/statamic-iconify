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

                    <div v-if="result" class="overflow-y-scroll flex-1 pr-6">

                        <table v-if="listType === 'table'" class="data-table w-full">
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

                        <Panel v-if="listType === 'grid'" class="grid grid-cols-8 gap-2">

                            <button v-for="icon in icons" :key="icon.name" class="bg-white dark:bg-gray-800 rounded-xl ring ring-gray-200 dark:ring-x-0 dark:ring-b-0 dark:ring-gray-700 relative aspect-square flex items-center justify-center group cursor-pointer" @click="select(icon)">

                                <iconify-icon :icon="icon.name" class="text-4xl text-gray-800 dark:text-white group-hover:scale-125 transition-all"></iconify-icon>

                                <div class="absolute bottom-0 left-0 right-0 p-2 bg-gray-50 text-xs text-gray-500 text-center rounded-b-lg opacity-0 group-hover:opacity-100 transition-opacity my-0">
                                    <span v-text="icon.name"></span>
                                </div>

                                <div class="absolute top-2 left-2 p-1 bg-gray-200 text-xs text-gray-600 rounded-sm opacity-0 group-hover:opacity-100 transition-opacity">
                                    <span v-text="icon.collection.license.title"></span>
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
import { ref, computed, getCurrentInstance, watch, nextTick } from 'vue';
import { getIcon, buildIcon } from 'iconify-icon';
import { FieldtypeMixin as Fieldtype } from '@statamic/cms';
import {
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

const icons = computed(() => {
    if (!result.value) return [];
    return result.value.icons.map(icon => ({
        name: icon,
        collection: result.value.collections[icon.split(':')[0]],
    }));
});

const { proxy } = getCurrentInstance();

watch(searchModalIsOpen, async (open) => {
    if (!open) return;
    await nextTick();
    // Allow Stack to finish rendering/animating before focusing.
    setTimeout(() => {
        const el = queryRef.value?.$el?.querySelector('input');
        if (el) el.focus();
    }, 50);
});

function openSearchModal() {
    searchModalIsOpen.value = true;
}

function search() {
    loading.value = true;
    const url = new URL('https://api.iconify.design/search');
    url.searchParams.set('limit', '999');
    url.searchParams.set('query', query.value);

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
<template>

    <div>
        <div v-if="value" class="iconify-flex iconify-items-center iconify-gap-4">
            <dropdown-list placement="bottom-center">
                <template #trigger>
                    <iconify-icon v-if="typeof value === 'string'" :icon="value" class="iconify-cursor-pointer iconify-text-4xl iconify-text-gray-800" v-tooltip="{content: value, delay: 500, autoHide: false}"></iconify-icon>

                    <svg v-else v-bind="value.attributes" class="iconify-cursor-pointer iconify-text-4xl iconify-text-gray-800" v-html="value.body" v-tooltip="{content: value.name, delay: 500, autoHide: false}"></svg>
                </template>
                <dropdown-item text="Change" @click="openSearchModal" />
                <dropdown-item text="Remove" @click="update(null)" />
            </dropdown-list>
        </div>

        <button v-else class="btn" @click="openSearchModal">Browse Iconify</button>

        <stack
            v-if="searchModalIsOpen"
            @closed="searchModalIsOpen = false"
        >
            <div slot-scope="{ close }" class="iconify-flex iconify-flex-col iconify-h-full iconify-bg-white">

                <header class="flex items-center sticky top-0 inset-x-0 bg-white shadow px-8 py-2 z-1 h-13">
                    <h1 class="flex-1 flex items-center text-xl">
                        Search and select an icon
                    </h1>
                    <button type="button" class="btn-close" @click="searchModalIsOpen = false">×</button>
                </header>

                <div class="iconify-px-3 md:iconify-px-8 iconify-py-4 iconify-pr-0 iconify-flex-1 iconify-flex iconify-flex-col iconify-overflow-hidden">
                    <div class="iconify-w-full iconify-flex iconify-gap-4 iconify-mb-4">
                        <text-input ref="query" v-model="query" class="iconify-flex-1" placeholder="Search for an icon..." @keydown.enter="search" />
                        <button class="btn-primary" @click="search" :disabled="loading">{{ loading ? 'Searching...' : 'Search' }}</button>
                        <div class="btn-group">
                            <button class="btn px-4" :class="{ active: listType === 'grid' }" @click="listType = 'grid'">
                                <iconify-icon icon="ph:grid-nine-light" class="iconify-text-xl"></iconify-icon>
                            </button>
                            <button class="btn px-4" :class="{ active: listType === 'table' }" @click="listType = 'table'">
                                <iconify-icon icon="ph:table-light" class="iconify-text-xl"></iconify-icon>
                            </button>
                        </div>
                    </div>

                    <div v-if="result" class="iconify-overflow-y-scroll iconify-flex-1 iconify-pr-6">

                        <table v-if="listType === 'table'" class="data-table iconify-w-full">
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
                                <tr v-for="icon in icons">
                                    <td>
                                        <iconify-icon :icon="icon.name" class="iconify-text-2xl iconify-text-gray-800"></iconify-icon>
                                    </td>
                                    <td>
                                        <span v-text="icon.name" class="iconify-text-sm"></span>
                                    </td>
                                    <td>
                                        <span v-text="icon.collection.name" class="iconify-text-sm"></span>
                                    </td>
                                    <td>
                                        <span v-text="icon.collection.license.title" class="iconify-text-sm"></span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm" @click="select(icon)">Select</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-if="listType === 'grid'" class="iconify-grid iconify-grid-cols-8 iconify-gap-4">

                            <button v-for="icon in icons" class="iconify-relative iconify-aspect-square iconify-bg-gray-100 iconify-rounded-lg iconify-flex iconify-items-center iconify-justify-center iconify-group" @click="select(icon)">

                                <iconify-icon :icon="icon.name" class="iconify-text-4xl iconify-text-gray-800 group-hover:iconify-scale-125 iconify-transition-all"></iconify-icon>

                                <div class="iconify-absolute iconify-bottom-0 iconify-left-0 iconify-right-0 iconify-p-2 iconify-bg-gray-50 iconify-text-xs iconify-text-gray-500 iconify-text-center iconify-rounded-b-lg iconify-opacity-0 group-hover:iconify-opacity-100 iconify-transition-opacity">
                                    <span v-text="icon.name"></span>
                                </div>

                                <div class="iconify-absolute iconify-top-2 iconify-left-2 iconify-p-1 iconify-bg-gray-200 iconify-text-xs iconify-text-gray-600 iconify-rounded-sm iconify-opacity-0 group-hover:iconify-opacity-100 iconify-transition-opacity">
                                    <span v-text="icon.collection.license.title"></span>
                                </div>

                            </button>

                        </div>
                    </div>

                </div>
                
                

            </div>
        </stack>

    </div>

</template>

<script>
import { getIcon, buildIcon } from 'iconify-icon';

export default {

    mixins: [Fieldtype],

    data() {
        return {
            searchModalIsOpen: false,
            listType: 'grid',
            query: '',
            result: null,
            loading: false,
        };
    },

    computed: {
        icons() {
            if (!this.result) return []

            return this.result.icons.map(icon => {
                return {
                    name: icon,
                    collection: this.result.collections[icon.split(':')[0]],
                }
            })
        }
    },

    methods: {
        openSearchModal() {
            this.searchModalIsOpen = true
            this.$wait(300).then(() => { this.$refs.query.$el.querySelector('input').focus() })
        },
        search() {
            this.loading = true
            ky.get('https://api.iconify.design/search?limit=999&query=' + this.query)
                .json()
                .then(data => {
                   this.result = data
                })
                .finally(() => {
                    this.loading = false
                })
        },
        getIconBuildData(icon) {
            const iconBuildData = buildIcon(getIcon(icon.name))

            const iconData = {
                name: icon.name,
            }

            Object.keys(iconBuildData).forEach(key => {
                iconData[key] = iconBuildData[key]
            })

            return iconData
        },
        select(icon) {
            if (this.config.store_as === 'name') {
                this.update(icon.name)
            } else if (this.config.store_as === 'svg_data') {
                this.update(this.getIconBuildData(icon))
            }

            this.searchModalIsOpen = false
        },
    }
};
</script>

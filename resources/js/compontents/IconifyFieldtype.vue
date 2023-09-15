<template>

    <div>
        <div v-if="value" class="iconify-flex iconify-items-center iconify-gap-4 iconify-group">
            <iconify-icon :icon="value" class="iconify-text-4xl iconify-text-gray-800" v-tooltip="{content: value, delay: 500, autoHide: false}"></iconify-icon>

            <dropdown-list class="iconify-invisible group-hover:iconify-visible">
                <dropdown-item text="Change" @click="searchModalIsOpen = true" />
                <dropdown-item text="Remove" @click="value = ''" />
            </dropdown-list>
        </div>

        <button v-else class="btn btn-xs" @click="searchModalIsOpen = true">Find an icon</button>

        <modal
            v-if="searchModalIsOpen"
            name="search-modal"
            :scrollable="true"
            @closed="searchModalIsOpen = false"
            @opened="modalOpened"
        >
            <div slot-scope="{ close }" class="iconify-p-4 iconify-flex iconify-flex-col iconify-h-[inherit]">
                <div class="iconify-flex iconify-justify-between iconify-items-center iconify-mb-4">
                    <h2>Pick an icon</h2>
                    <button class="btn" @click="close">x</button>
                </div>
                
                <div class="iconify-w-full iconify-flex iconify-gap-4 iconify-mb-4">
                    <text-input ref="query" v-model="query" class="iconify-flex-1" @keydown.enter="search" />
                    <button class="btn-primary" @click="search">Search</button>
                </div>

                <div v-if="result" class="iconify-overflow-y-scroll iconify-flex-1">


                    <table class="data-table iconify-w-full">
                        <tbody>
                            <tr v-for="icon in result.icons">
                                <td>
                                    <iconify-icon :icon="icon" class="iconify-text-2xl iconify-text-gray-800"></iconify-icon>
                                </td>
                                <td>
                                    <span v-text="icon" class="iconify-text-sm"></span>
                                </td>
                                <td>
                                    <button class="btn" @click="select(icon)">Pick</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </modal>

    </div>

</template>

<script>
export default {

    mixins: [Fieldtype],

    data() {
        return {
            searchModalIsOpen: false,
            query: '',
            result: null
        };
    },

    methods: {
        search() {
            ky.get('https://api.iconify.design/search?limit=999&query=' + this.query)
                .json()
                .then(data => {
                   this.result = data
                   console.log(data)
                })
        },
        select(icon) {
            this.$emit('input', icon)
            this.searchModalIsOpen = false
        },
        modalOpened() {
            this.$refs.query.$el.querySelector('input').focus()
        }
    }

};
</script>

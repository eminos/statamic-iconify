// Mock for @statamic/cms
export const FieldtypeMixin = {
    props: {
        value: { default: null },
        config: { type: Object, default: () => ({}) },
        meta: { type: Object, default: () => ({}) },
        handle: { type: String },
        fieldPathPrefix: { type: String },
        namePrefix: { type: String },
        hasError: { type: Boolean, default: false },
    },
    methods: {
        update(value) {
            this.$emit('input', value);
        },
    },
};

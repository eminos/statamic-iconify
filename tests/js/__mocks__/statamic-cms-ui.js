// Mock Statamic UI components
import { defineComponent, h } from 'vue';

const stub = (name) => defineComponent({
    name,
    render() {
        return h('div', { class: `mock-${name}` }, this.$slots.default?.());
    },
});

export const Button = stub('Button');
export const Input = stub('Input');
export const Dropdown = stub('Dropdown');
export const DropdownMenu = stub('DropdownMenu');
export const DropdownItem = stub('DropdownItem');
export const ToggleGroup = stub('ToggleGroup');
export const ToggleItem = stub('ToggleItem');
export const Panel = stub('Panel');
export const Stack = stub('Stack');
export const StackContent = stub('StackContent');
export const StackClose = stub('StackClose');

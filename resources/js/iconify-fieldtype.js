import 'iconify-icon';

import IconifyFieldtype from './compontents/IconifyFieldtype.vue';

Statamic.booting(() => {
    Statamic.$components.register('iconify-fieldtype', IconifyFieldtype);
});

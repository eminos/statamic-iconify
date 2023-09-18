<?php

namespace StatamicIconify\Fieldtypes;

use Statamic\Fields\Fieldtype;

class IconifyFieldtype extends Fieldtype
{
    /**
     * The blank/default value.
     *
     * @return array
     */
    public function defaultValue()
    {
        return null;
    }

    /**
     * Pre-process the data before it gets sent to the publish page.
     *
     * @param mixed $data
     * @return array|mixed
     */
    public function preProcess($data)
    {
        return $data;
    }

    /**
     * Process the data before it gets saved.
     *
     * @param mixed $data
     * @return array|mixed
     */
    public function process($data)
    {
        return $data;
    }

    protected function configFieldItems(): array
    {
        return [
            'store_as' => [
                'display' => 'Store icon as',
                'instructions' => 'Choose how the selected icon should be stored.',
                'type' => 'button_group',
                'default' => 'name',
                'options' => [
                    'name' => 'Icon name',
                    'svg_data' => 'SVG data',
                ],
                'width' => 50
            ],
        ];
    }
}

<?php

namespace StatamicIconify\Tags;

use Statamic\Tags\Tags;
use Statamic\Fields\Value;
 
class IconifyTag extends Tags
{
    protected static $handle = 'iconify';

    public function wildcard($fieldName)
    {
        /**
         * @var Value $fieldValue
         */
        $fieldValue = array_get($this->context, $fieldName);

        $rawValue = $fieldValue->raw();

        if (is_array($rawValue) && array_key_exists('body', $rawValue)) {
            return $this->renderSVG($rawValue);
        } else {
            return $rawValue;
        }
    }

    protected function renderSVG($icon)
    {
        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg"%s>%s</svg>', 
            $this->getSVGTagAttributes($icon),
            $icon['body']
        );

        return $svg;
    }

    protected function getSVGTagAttributes($icon)
    {
        $attributesString = '';

        $params = array_merge($icon['attributes'], $this->params->all());

        foreach ($params as $key => $value) {
            $attributesString .= sprintf(' %s="%s"', $key, $value);
        }
        
        return $attributesString;
    }
}
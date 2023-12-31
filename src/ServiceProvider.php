<?php

namespace StatamicIconify;

use StatamicIconify\Tags\IconifyTag;
use Statamic\Providers\AddonServiceProvider;
use StatamicIconify\Fieldtypes\IconifyFieldtype;
 
class ServiceProvider extends AddonServiceProvider
{

    public function __construct()
    {
        $this->vite['hotFile'] = base_path('vendor/eminos/statamic-iconify/dist/vite.hot');

        parent::__construct(app());
    }

    protected $fieldtypes = [
        IconifyFieldtype::class,
    ];

    protected $tags = [
        IconifyTag::class,
    ];

    protected $vite = [
        'hotFile' => null, // set in the constructor for reasons
        'publicDirectory' => 'dist',
        'input' => [
            'resources/js/iconify-fieldtype.js',
            'resources/css/iconify-fieldtype.css'
        ],
    ];

    public function bootAddon()
    {

        // 
        
    }
}
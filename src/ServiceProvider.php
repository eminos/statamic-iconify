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
        'hotFile' => null,
        'publicDirectory' => 'dist',
        'input' => [
            'resources/js/iconify-fieldtype.js',
            'resources/css/iconify-fieldtype.css',
        ],
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    public function bootAddon()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/statamic-iconify.php', 'statamic-iconify');

        $this->publishes([
            __DIR__.'/../config/statamic-iconify.php' => config_path('statamic-iconify.php'),
        ], 'statamic-iconify-config');
    }
}

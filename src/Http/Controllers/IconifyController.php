<?php

namespace StatamicIconify\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class IconifyController extends Controller
{
    /**
     * Return the current global config (for the Vue component).
     */
    public function config(): JsonResponse
    {
        return response()->json([
            'allowed_prefixes' => config('statamic-iconify.allowed_prefixes', []),
            'allowed_categories' => config('statamic-iconify.allowed_categories', []),
            'allowed_licenses' => config('statamic-iconify.allowed_licenses', []),
            'default_store_as' => config('statamic-iconify.default_store_as', 'name'),
        ]);
    }
}

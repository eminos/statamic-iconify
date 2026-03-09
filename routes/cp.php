<?php

use Illuminate\Support\Facades\Route;
use StatamicIconify\Http\Controllers\IconifyController;

Route::get('iconify/config', [IconifyController::class, 'config'])
    ->name('statamic-iconify.config');

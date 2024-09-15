<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::controller(HomeController::class)->group(function ($home) {
    $home->get('/', 'index');
    $home->post('/check-url', 'checkUrl');
});

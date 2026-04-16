<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'success' => true,
        'data' => [
            'service' => 'bunyan-backend',
        ],
        'message' => 'OK',
        'errors' => (object) [],
    ]);
});

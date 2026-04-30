<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => 'StudentAPI',
        'message' => 'Use /api/students for the REST API endpoints.',
        'endpoints' => [
            'GET /api/students',
            'POST /api/students',
            'GET /api/students/{id}',
            'PUT /api/students/{id}',
            'DELETE /api/students/{id}',
        ],
    ]);
});

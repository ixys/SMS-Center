<?php

use App\Http\Controllers\Api\MessageIngestController;
use Illuminate\Support\Facades\Route;

Route::post('/messaging/{platformAccount}/incoming', [MessageIngestController::class, 'incoming'])
     ->name('messaging.incoming');

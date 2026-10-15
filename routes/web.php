<?php

use App\Http\Controllers\CampaignController;
use Illuminate\Support\Facades\Route;




Route::get('/campaigns' , [CampaignController::class , 'index']);

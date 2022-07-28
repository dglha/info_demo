<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', 'MissionController@test');
Route::get('/get', 'MissionController@getData');
Route::post('/ms', 'MissionController@postMission');
Route::get('/ms', 'MissionController@getMission');
Route::post('/code', 'MissionController@generateCode');
Route::post('/key', 'MissionController@pasteKey');

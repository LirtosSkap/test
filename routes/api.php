<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register','Api\UsersController@create');
Route::post('login','Api\UsersController@login');
Route::post('logout','Api\UsersController@logout');
Route::get('users','Api\UsersController@index');


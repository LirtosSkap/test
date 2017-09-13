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

Route::group([
    'as' => 'user::',
    'namespace' => '\Api'
    ], function () {

    Route::post('register', [
        'as' => 'register',
        'uses' => 'UsersController@create'
    ]);
    Route::post('login', [
        'as' => 'login',
        'uses' => 'UsersController@login'
    ]);
    Route::post('logout', [
        'as' => 'logout',
        'uses' => 'UsersController@logout'
    ]);
    Route::post('update', [
        'as' => 'update',
        'uses' => 'UsersController@update'
    ]);
    Route::post('photo', [
        'as' => 'updatePhoto',
        'uses' => 'UsersController@uploadPhoto'
    ]);
    Route::post('like', [
        'as' => 'like',
        'uses' => 'UsersController@like'
    ]);

    Route::get('users', [
        'as' => 'getAll',
        'uses' => 'UsersController@index'
    ]);
    Route::get('user', [
        'as' => 'getCurrent',
        'uses' => 'UsersController@getCurrentUserInfo'
    ]);
    Route::post('userByID', [
        'as' => 'getByID',
        'uses' => 'UsersController@getSpecificUserInfo'
    ]);
});

<?php

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

//Auth::loginUsingId(2);

Route::get('/', function () {
    return Auth::check() ? redirect()->route('home') : redirect()->route('login');
});

Auth::routes();

Route::group(['middleware' => 'auth'], function() {
    Route::get('/home', 'HomeController@index')->name('home');

    Route::get('/profile', 'ProfileController@index');
    Route::post('/profile', 'ProfileController@update');
    Route::post('/profile/password', 'ProfileController@password');
    Route::post('/profile/address/{type}', 'ProfileController@address');
    Route::post('/profile/phone/{type}', 'ProfileController@phone');
});

Route::group([
    'as' => 'business.',
    'prefix' => 'business',
    'middleware' => ['auth', 'roles'],
    'roles' => ['office_user'],
], function() {
    Route::resource('clients', 'Business\ClientController');
    Route::post('clients/{id}/address/{type}', 'Business\ClientController@address')->name('clients.address');
    Route::post('/clients/{id}/phone/{type}', 'Business\ClientController@phone')->name('clients.phone');
    Route::get('clients/{id}/schedule', 'Business\ClientScheduleController@index')->name('clients.schedule');
    Route::post('clients/{id}/schedule', 'Business\ClientScheduleController@create')->name('clients.schedule.create');
    Route::post('clients/{id}/schedule/single', 'Business\ClientScheduleController@createSingle')->name('clients.schedule.create.single');
    Route::get('clients/{id}/schedule/{schedule_id}', 'Business\ClientScheduleController@show')->name('clients.schedule.show');
    Route::patch('clients/{id}/schedule/{schedule_id}', 'Business\ClientScheduleController@update')->name('clients.schedule.update');
    Route::patch('clients/{id}/schedule/{schedule_id}/single', 'Business\ClientScheduleController@updateSingle')->name('clients.schedule.update.single');
    Route::post('clients/{id}/schedule/{schedule_id}/delete', 'Business\ClientScheduleController@destroy')->name('clients.schedule.destroy');
    Route::post('clients/{id}/schedule/{schedule_id}/single/delete', 'Business\ClientScheduleController@destroySingle')->name('clients.schedule.destroy.single');

});
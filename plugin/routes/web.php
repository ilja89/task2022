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

Route::get('/', function () {
    return view('welcome');
});

Route::get('instance_form', 'InstanceFormController@index');
Route::get('view.php', 'AssignmentController@index')
        ->middleware('auth.requireEnrolment');
Route::get('courses/{course}/settings', 'CourseSettingsFormController@index');
Route::post('courses/{course}/settings', 'CourseSettingsController@store');
Route::get('popup', 'PopupController@index');

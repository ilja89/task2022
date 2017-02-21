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

Route::post('charons', 'InstanceController@store');
Route::post('charons/update', 'InstanceController@update');
Route::post('charons/delete', 'InstanceController@delete');

Route::get('instance_form', 'InstanceFormController@index');
Route::post('instance_form', 'InstanceFormController@postIndex');
Route::get('view.php', 'AssignmentController@index')
        ->middleware('auth.requireEnrolment');
Route::get('courses/{course}/settings', 'CourseSettingsFormController@index')
        ->middleware('auth.requireCourseManagement');
Route::post('courses/{course}/settings', 'CourseSettingsController@store')
        ->middleware('auth.requireCourseManagement');
Route::get('courses/{course}/popup', 'PopupController@index')
        ->middleware('auth.requireCourseManagement');

// For handling Moodle requests before sending to controllers from lib.php.
Route::get('course/modedit.php', function () { return ''; });
Route::post('course/modedit.php', function () { return ''; });

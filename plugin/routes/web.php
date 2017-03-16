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
Route::get('documentation', 'StaticPagesController@apiDocumentation');

Route::post('charons', 'InstanceController@store');
Route::post('charons/update', 'InstanceController@update');

Route::post('post_create/{charonId}', 'InstanceController@postCourseModuleCreated');
Route::post('post_update/{charonId}', 'InstanceController@postCourseModuleUpdated');

Route::get('instance_form', 'InstanceFormController@index');
Route::post('instance_form', 'InstanceFormController@postIndex');
Route::middleware('auth.requireEnrolment')
    ->get('view.php', 'AssignmentController@index');
Route::middleware('auth.requireCourseManagement')
    ->get('courses/{course}/settings', 'CourseSettingsFormController@index');
Route::middleware('auth.requireCourseManagement')
    ->post('courses/{course}/settings', 'CourseSettingsController@store');
Route::middleware('auth.requireCourseManagement')
    ->get('courses/{course}/popup', 'PopupController@index');

// For handling Moodle requests before sending to controllers from lib.php.
Route::get('course/modedit.php', function () { return ''; });
Route::post('course/modedit.php', function () { return ''; });

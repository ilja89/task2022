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
Route::get('clear_opcache', function () {
    // Helper to make deployment easier
    opcache_reset();
});

Route::get('documentation', 'StaticPagesController@apiDocumentation');

Route::post('charons', 'InstanceController@store');
Route::post('charons/update', 'InstanceController@update');

Route::post('post_create/{charonId}', 'InstanceController@postCourseModuleCreated');
Route::post('post_update/{charonId}', 'InstanceController@postCourseModuleUpdated');

Route::get('instance_form', 'InstanceFormController@index');
Route::post('instance_form', 'InstanceFormController@postIndex');
Route::middleware('auth.course_module.enrolment.require')
    ->get('view.php', 'AssignmentController@index');
Route::middleware('auth.course.managing.require')
    ->get('courses/{course}/settings', 'CourseSettingsFormController@index');
Route::middleware('auth.course.managing.require')
    ->post('courses/{course}/settings', 'CourseSettingsController@store');
Route::middleware('auth.course.managing.require')
    ->get('courses/{course}/popup', 'PopupController@index');

// For handling Moodle requests before sending to controllers from lib.php. Might not need these!
Route::get('course/modedit.php', function () { return ''; });
Route::post('course/modedit.php', function () { return ''; });


Route::get('/courses/{course}/popup/labsForm', 'PopupController@insertForm');
Route::post('/courses/{course}/popup/labs', 'LabsController@insert');


Route::middleware('auth.course_module.enrolment.require')
    ->post('view.php', 'SubmissionController@insert');

Route::middleware('auth.course_module.enrolment.require')
    ->get('api/view.php', 'CharonController@get');


Route::get('api/charon_data.php', 'CharonController@getAll');

Route::get('api/labs_by_charon.php', 'LabsController@findLabsByCharonLaterEqualToday');
Route::get('api/student_defense_data.php', 'CharonController@getDefenders');
Route::get('api/get_time.php', 'SubmissionController@getRowCountForPractise');
Route::delete('api/delete_defense.php', 'DefenseLabController@deleteReg');

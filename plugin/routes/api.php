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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post('tester_callback', 'TesterCallbackController@index')
    ->name('tester_callback');
Route::get('git_callback', 'GitCallbackController@index');

Route::get('courses/{course}/students/search', 'Api\StudentsController@searchStudents')
    ->middleware('auth.requireCourseManagement');
Route::get('courses/{course}/charons', 'Api\PopupController@getCharonsByCourse');

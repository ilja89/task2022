<?php

Route::group(['namespace' => 'Api'], function () {

    Route::post('tester_callback', 'TesterCallbackController@index')
         ->name('tester_callback');
    Route::get('git_callback', 'GitCallbackController@index')
         ->name('git_callback');

    Route::middleware('auth.course.managing.require')
        ->get('courses/{course}/students/search', 'StudentsController@searchStudents');
    Route::middleware('auth.course.managing.require')
        ->get('courses/{course}/charons', 'CharonsController@getByCourse');
    Route::get('charons/{charon}/submissions', 'SubmissionsController@getByCharon');
    Route::middleware('auth.charon.managing.require')
        ->get('charons/{charon}/submissions/{submissionId}', 'SubmissionsController@findById');

    // No middleware - used in assignment view too! Should probably use require login tho
    Route::get('submissions/{submission}/files', 'FilesController@index');

    Route::middleware('auth.submission.managing.require')
         ->get('submissions/{submission}/outputs', 'SubmissionsController@getOutputs');
    Route::middleware('auth.charon.managing.require')
         ->post('charons/{charon}/submissions/add', 'SubmissionsController@addNewEmpty');
    Route::middleware('auth.charon.managing.require')
        ->post('charons/{charon}/submissions/{submission}', 'SubmissionsController@saveSubmission');
    Route::middleware('auth.charon.managing.require')
        ->post('charons/{charon}/comments', 'CommentsController@saveComment');
    Route::middleware('auth.charon.managing.require')
        ->get('charons/{charon}/comments', 'CommentsController@getByCharonAndStudent');
    Route::middleware('auth.course.managing.require')
        ->get('courses/{course}/users/{userId}', 'StudentsController@findById');
    Route::middleware('auth.course.managing.require')
         ->get('courses/{course}/users/{user}/report-table', 'StudentsController@getStudentReportTable');
    Route::middleware('auth.charon.managing.require')
        ->get('charons/{charon}/results/{user}', 'StudentsController@getStudentActiveResultForCharon');

    Route::middleware('auth.course.managing.require')
        ->post('courses/{course}/presets', 'PresetsController@store');
    Route::middleware('auth.course.managing.require')
        ->put('courses/{course}/presets/{preset}', 'PresetsController@update');
});

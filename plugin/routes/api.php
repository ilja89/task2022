<?php

Route::group(['namespace' => 'Api'], function () {

    Route::post('tester_callback', 'TesterCallbackController@index')
         ->name('tester_callback');
    Route::get('git_callback', 'GitCallbackController@index')
         ->name('git_callback');

    Route::middleware('auth.requireCourseManagement')
        ->get('courses/{course}/students/search', 'StudentsController@searchStudents');
    Route::middleware('auth.requireCourseManagement')
        ->get('courses/{course}/charons', 'PopupController@getCharonsByCourse');
    Route::middleware('auth.requireCharonManaging')
        ->get('charons/{charon}/submissions', 'PopupController@getSubmissionsByCharon');
    Route::middleware('auth.requireCharonManaging')
        ->get('charons/{charon}/submissions/{submissionId}', 'SubmissionsController@findById');
    Route::get('submissions/{submission}/files', 'FilesController@index');
//         ->middleware('auth.requireSubmissionManaging');
    Route::get('submissions/{submission}/outputs', 'SubmissionsController@getOutputs');
//         ->middleware('auth.requireSubmissionManaging');
    Route::middleware('auth.requireCharonManaging')
         ->post('charons/{charon}/submissions/add', 'SubmissionsController@addNewEmpty');
    Route::middleware('auth.requireCharonManaging')
        ->post('charons/{charon}/submissions/{submission}', 'PopupController@saveSubmission');
    Route::middleware('auth.requireCharonManaging')
        ->post('charons/{charon}/comments', 'PopupController@saveComment');
    Route::middleware('auth.requireCharonManaging')
        ->get('charons/{charon}/comments', 'PopupController@getComments');
    Route::middleware('auth.requireCourseManagement')
        ->get('courses/{course}/users/{userId}', 'StudentsController@findById');

    Route::middleware('auth.requireCourseManagement')
        ->post('courses/{course}/presets', 'PresetsController@store');
    Route::middleware('auth.requireCourseManagement')
        ->put('courses/{course}/presets/{preset}', 'PresetsController@update');
});

<?php

Route::group(['namespace' => 'Api'], function () {

    Route::post('tester_callback', 'TesterCallbackController@index')
         ->name('tester_callback');
    Route::get('git_callback', 'GitCallbackController@index')
         ->name('git_callback');

    Route::get('courses/{course}/students/search', 'StudentsController@searchStudents')
         ->middleware('auth.requireCourseManagement');
    Route::get('courses/{course}/charons', 'PopupController@getCharonsByCourse')
         ->middleware('auth.requireCourseManagement');
    Route::get('charons/{charon}/submissions', 'PopupController@getSubmissionsByCharon')
         ->middleware('auth.requireCharonManaging');
    Route::post('charons/{charon}/submissions/{submission}', 'PopupController@saveSubmission')
         ->middleware('auth.requireCharonManaging');
    Route::post('charons/{charon}/comments', 'PopupController@saveComment')
         ->middleware('auth.requireCharonManaging');
    Route::get('charons/{charon}/comments', 'PopupController@getComments')
         ->middleware('auth.requireCharonManaging');

    Route::post('courses/{course}/presets', 'PresetsController@store')
        ->middleware('auth.requireCourseManagement');
    Route::put('courses/{course}/presets/{preset}', 'PresetsController@update')
        ->middleware('auth.requireCourseManagement');
});

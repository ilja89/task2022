<?php

Route::group(['namespace' => 'Api'], function () {

    Route::post('tester_callback', 'Api\TesterCallbackController@index')
         ->name('tester_callback');
    Route::get('git_callback', 'Api\GitCallbackController@index')
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
});

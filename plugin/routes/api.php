<?php

Route::group(['namespace' => 'Api'], function () {

    Route::post('tester_callback', 'TesterCallbackController@index')
         ->name('tester_callback');
    Route::get('git_callback', 'GitCallbackController@index')
         ->name('git_callback');
    Route::post('git_callback', 'GitCallbackController@indexPost')
        ->name('git_callback_post');

    Route::middleware('auth.course.managing.require')
        ->get('courses/{course}/students/search', 'StudentsController@searchStudents');
    Route::middleware('auth.course.managing.require')
        ->get('courses/{course}/charons', 'CharonsController@getByCourse');
    Route::middleware('auth.charon.submissions.view.require')
        ->get('charons/{charon}/submissions', 'SubmissionsController@getByCharon');
    Route::middleware('auth.submission.managing.require')
        ->get('submissions/{submission}', 'SubmissionsController@findById');

    // No middleware - used in assignment view too! Should probably use require login tho
    // TODO: Add middleware to check if user sees their own files or is teacher
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

    Route::middleware('auth.charon.managing.require')
        ->post('charons/{charon}/checksuite/run', 'PlagiarismController@runChecksuite');
    Route::middleware('auth.charon.managing.require')
        ->get('charons/{charon}/similarities', 'PlagiarismController@fetchSimilarities');

    Route::middleware('auth.course.managing.require')
        ->get('courses/{course}/users/active', 'StudentsController@findActive');
    Route::middleware('auth.course.managing.require')
        ->get('courses/{course}/users/distribution', 'StudentsController@findDistribution');
    Route::middleware('auth.course.managing.require')
        ->get('courses/{course}/users/{userId}', 'StudentsController@getStudentInfo');
    Route::middleware('auth.course.managing.require')
         ->get('courses/{course}/users/{user}/report-table', 'StudentsController@getStudentReportTable');
    Route::middleware('auth.charon.managing.require')
        ->get('charons/{charon}/results/{user}', 'StudentsController@getStudentActiveResultForCharon');
    Route::middleware('auth.course.managing.require')
         ->get('courses/{course}/users/{user}/groups', 'StudentsController@getStudentGroups');

    Route::middleware('auth.course.managing.require')
        ->post('courses/{course}/presets', 'PresetsController@store');
    Route::middleware('auth.course.managing.require')
        ->put('courses/{course}/presets/{preset}', 'PresetsController@update');

    Route::middleware('auth.submission.managing.require')
         ->post('submissions/{submission}/retest', 'RetestController@index');

    Route::middleware('auth.course.managing.require')
        ->get('courses/{course}/users/{user}/submissions', 'SubmissionsController@getByUser');
    Route::middleware('auth.course.managing.require')
        ->get('courses/{course}/submissions/latest', 'SubmissionsController@findLatest');
    Route::middleware('auth.course.managing.require')
        ->get('courses/{course}/submissions/counts', 'SubmissionsController@findSubmissionCounts');
    Route::middleware('auth.course.managing.require')
        ->get('courses/{course}/submissions/average', 'SubmissionsController@findBestAverageCourseSubmissions');
    Route::middleware('auth.course.managing.require')
        ->get('courses/{course}/submissions/submissions-report/{page}/{perPage}/{sortField}/{sortType}/{firstName?}/' .
            '{lastName?}/{exerciseName?}/{isConfirmed?}/{gitTimestampForStartDate?}/{gitTimestampForEndDate?}',
            'SubmissionsController@findAllSubmissionsForReport');

    // LAB STUFF


    Route::middleware('auth.course.managing.require')  // save lab
        ->post('courses/{course}/labs', 'LabController@save');
    Route::middleware('auth.course.managing.require')  // get all labs for course - works. Usage: /labs page and new charon adding
        ->get('courses/{course}/labs', 'LabController@getByCourse');
    Route::middleware('auth.charon.managing.require')  // get all labs for charon - works - charon_defense_lab. Usage: register for defense
        ->get('charons/{charon}/labs', 'CharonDefenseLabController@getByCharon');
    Route::middleware('auth.course.managing.require')  // get teachers in a lab - works. Usage: /labs page
        ->get('courses/{course}/labs/{lab}/teachers', 'LabTeacherController@getByLab');
    Route::middleware('auth.charon.managing.require')  // get teachers in a specific charon lab - works. Usage: register for defense
        ->get('charons/{charon}/labs/{charon_defense_lab}/teachers', 'LabTeacherController@getByCharonAndLab');

});

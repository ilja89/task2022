<?php

Route::group(['namespace' => 'Api'], function () {

    Route::middleware('auth.course.managing.require')
        ->get('courses/{course}/testerTypes/all', 'ClassificationsController@getAllTesterTypes');
    Route::middleware('auth.course.managing.require')
        ->post('courses/{course}/testerTypes/add/{name}', 'ClassificationsController@saveTesterType');
    Route::middleware('auth.course.managing.require')
        ->delete('courses/{course}/testerTypes/remove/{name}', 'ClassificationsController@removeTesterType');

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
    Route::middleware('auth.course.managing.require')
        ->get('courses/{course}/logs', 'CharonsController@getLogsById');
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
        ->delete('charons/{charon}', 'CharonsController@deleteById');
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
    Route::middleware('auth.course.managing.require')  // get all labs for course
    ->get('courses/{course}/labs', 'LabController@getByCourse');
    Route::middleware('auth.charon.managing.require')
        ->get('charons/{charon}labs', 'LabController@getByCharon');
    Route::middleware('auth.charon.managing.require')  // get all labs for charon
    ->get('charons/{charon}/labs', 'CharonDefenseLabController@getByCharon');
    Route::middleware('auth.course.managing.require')  // delete lab
    ->delete('courses/{course}/labs/{lab}', 'LabController@delete');
    Route::middleware('auth.course.managing.require')  // update lab
    ->post('courses/{course}/labs/{lab}/update', 'LabController@update');

    Route::middleware('auth.course_module.enrolment.require')
        ->get('api/view.php', 'CharonController@get');

    Route::get('api/charon_data.php', 'CharonController@getAll');

    Route::get('api/labs_by_charon.php', 'LabsController@findLabsByCharon');
    Route::middleware('auth.course.managing.require')  // update lab
    ->post('courses/{course}/labs/{lab}/update', 'LabController@update');

    // TEACHERS

    Route::middleware('auth.course.managing.require')  // get teachers
    ->get('courses/{course}/teachers', 'LabTeacherController@getTeachersByCourse');
    Route::middleware('auth.course.managing.require')  // get aggregated teachers
    ->get('courses/{course}/teachers/report', 'LabTeacherController@getTeacherReportByCourse');
    Route::middleware('auth.course.managing.require')  // get teachers in a lab
    ->get('courses/{course}/labs/{lab}/teachers', 'LabTeacherController@getByLab');
    Route::middleware('auth.charon.managing.require')  // get teachers in a specific charon lab
    ->get('charons/{charon}/labs/{lab}/teachers', 'LabTeacherController@getByCharonAndLab');
    Route::middleware('auth.course.managing.require')  // get teachers in a specific charon lab
    ->get('courses/{course}/teacher/{teacher}', 'LabTeacherController@getByTeacher');
    Route::middleware('auth.course.managing.require')  // get teachers in a specific charon lab
    ->get('courses/{course}/teacher/{teacher}/aggregated', 'LabTeacherController@getTeacherAggregatedData');
    Route::middleware('auth.course.managing.require') // update teacher data for lab
    ->put('course/{course}/labs/{lab}/teachers/{teacher}', 'LabTeacherController@updateTeacherForLab');

    // COURSE

    Route::middleware('auth.course.managing.require') // get a course
    ->get('courses/{course}', 'LabController@getCourse');

    // CHARON DEFENSE

    Route::middleware('auth.charon.managing.require') // save Charon defending stuff
    ->post('charons/{charon}', 'CharonsController@saveCharonDefendingStuff');

    Route::middleware('auth.charon.submissions.view.require') // get all registrations for student
    ->get('charons/{charon}/registrations', 'DefenseRegistrationController@getStudentRegistrations');

    Route::middleware('auth.charon.submissions.view.require')  // delete defense registration
    ->delete('charons/{charon}/registration', 'DefenseRegistrationController@deleteReg');

    Route::middleware('auth.course.managing.require') // get all charon defense registrations for course
    ->get('courses/{course}/defenseRegistrations', 'DefenseRegistrationController@getDefenseRegistrationsByCourse');

    Route::middleware('auth.course.managing.require') // get all charon defense registrations for course
    ->get('courses/{course}/defenseRegistrations/{after}/{before}/{teacher_id}/{progress}', 'DefenseRegistrationController@getDefenseRegistrationsByCourseFiltered');

    Route::middleware('auth.course.managing.require')  // get teacher for student
    ->get('courses/{course}/defenseRegistrations/student/{user}/teacher', 'LabTeacherController@getTeacherForStudent');

    Route::middleware('auth.course.managing.require')  // save defense progress
    ->put('courses/{course}/registration/{registration}', 'DefenseRegistrationController@saveProgress');

    Route::middleware('auth.charon.submissions.view.require') // add registration
        ->post('charons/{charon}/submission', 'SubmissionController@insert');

    Route::get('charons/{charon}/all', 'CharonController@getAll');

    Route::get('charons/{charon}/labs', 'LabsController@findLabsByCharonLaterEqualToday');

    Route::middleware('auth.charon.submissions.view.require')
        ->get('charons/{charon}/labs/unavailable', 'SubmissionController@getUnavailableTimes');

});

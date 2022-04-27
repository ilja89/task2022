<?php

Route::group(['namespace' => 'Api'], function () {

    Route::middleware('auth.course.managing.require')
        ->get('courses/{course}/testerTypes/all', 'ClassificationsController@getAllTesterTypes');
    Route::middleware('auth.course.managing.require')
        ->post('courses/{course}/testerTypes/add/{name}', 'ClassificationsController@saveTesterType');
    Route::middleware('auth.course.managing.require')
        ->delete('courses/{course}/testerTypes/remove/{name}', 'ClassificationsController@removeTesterType');
    Route::middleware('auth.course.managing.require')
        ->get('courses/{course}/testerType/{code}', 'ClassificationsController@getCharonTesterLanguage');

    Route::middleware('auth.course_module.enrolment.require')
        ->post('submissions/{charon}/postSubmission', 'TesterController@postSubmission');
    Route::post('submissions/saveResults', 'TesterController@saveResults');

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
    Route::middleware('auth.charon.submissions.view.require')  // query param user_id
        ->get('charons/{charon}/submissions', 'SubmissionsController@getByCharonAndUser');
    Route::middleware('auth.submission.managing.require')
        ->get('submissions/{submission}', 'SubmissionsController@findById');

    Route::middleware('auth.submission.managing.require')
        ->get('submissions/{submission}/files', 'FilesController@index');
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
        ->post('charons/{charon}/reviewComments/add', 'ReviewCommentController@add');
    Route::middleware('auth.charon.managing.require')
        ->delete('charons/{charon}/reviewComments/{reviewComment}/delete', 'ReviewCommentController@delete');
    Route::middleware('auth.charon.submissions.view.require') // clear review comments' notifications
        ->put('charons/{charon}/reviewComments/clear', 'ReviewCommentController@clearNotifications');
    Route::middleware('auth.charon.submissions.view.require')
        ->get('charons/{charon}/reviewComments/student', 'ReviewCommentController@getReviewCommentsForCharonAndStudent');

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
    Route::middleware('auth.charon.managing.require')
        ->get('charons/{charon}/retest', 'RetestController@retestByCharon');

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

    // LABS

    Route::get('charons/{charon}/labs/view', 'LabController@findLabsByCharonLaterEqualToday'); // get labs student can register to
    Route::middleware('auth.course.managing.require')  // save lab
        ->post('courses/{course}/labs', 'LabController@save');
    Route::middleware('auth.course.managing.require')  // get all labs for course
        ->get('courses/{course}/labs', 'LabController@getByCourse');
    Route::middleware('auth.charon.managing.require')  // get all labs for charon
        ->get('charons/{charon}/labs', 'CharonDefenseLabController@getByCharon');
    Route::middleware('auth.course.managing.require')  // delete lab
        ->delete('courses/{course}/labs/{lab}', 'LabController@delete');
    Route::middleware('auth.course.managing.require')  // update lab
        ->post('courses/{course}/labs/{lab}/update', 'LabController@update');
    Route::middleware('auth.course.managing.require')
        ->get('courses/{course}/labs/{lab}/registrations', 'LabController@countRegistrations'); 
        // get number of affected registrations when lab is being to deleted or modified

    // TEACHERS

    Route::middleware('auth.course.managing.require')  // get teachers
        ->get('courses/{course}/teachers', 'LabTeacherController@getTeachersByCourse');
    Route::middleware('auth.course.managing.require')  // get aggregated teachers
        ->get('courses/{course}/teachers/report', 'LabTeacherController@getTeacherReportByCourse');
    Route::middleware('auth.course.managing.require')  // get aggregated teachers
        ->get('courses/{course}/teachers/summary', 'LabTeacherController@getTeacherSummaryByCourse');
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

    // GROUPS

    Route::middleware('auth.course.managing.require') // get groups for course
        ->get('courses/{course}/groups', 'LabController@getGroups');

    // CHARON

    Route::middleware('auth.charon.managing.require') // save Charon
        ->post('charons/{charon}', 'CharonsController@saveCharon');
    Route::get('charons/{charon}', 'CharonsController@getFull'); // get a whole charon

    // CHARON DEFENSE

    Route::middleware('auth.charon.submissions.view.require') // get all registrations for student. query param user_id
        ->get('charons/{charon}/registrations', 'DefenseRegistrationController@getStudentRegistrations');

    Route::middleware('auth.charon.submissions.view.require')  // delete defense registration. query param user_id
        ->delete('charons/{charon}/registration', 'DefenseRegistrationController@delete');

    Route::middleware('auth.course.managing.require') // get all charon defense registrations for course
        ->get('courses/{course}/defenseRegistrations', 'DefenseRegistrationController@getDefenseRegistrationsByCourse');

    Route::middleware('auth.course.managing.require') // get all charon defense registrations for course
        ->get('courses/{course}/defenseRegistrations/{after}/{before}/{teacher_id}/{progress}', 'DefenseRegistrationController@getDefenseRegistrationsByCourseFiltered');

    Route::middleware('auth.course.managing.require')  // get teacher for student
        ->get('courses/{course}/defenseRegistrations/student/{user}/teacher', 'LabTeacherController@getTeacherForStudent');

    Route::middleware('auth.course.managing.require')  // save defense progress
        ->put('courses/{course}/registration/{registration}', 'DefenseRegistrationController@saveProgress');

    Route::middleware('auth.charon.submissions.view.require') // add registration
        ->post('charons/{charon}/submission', 'DefenseRegistrationController@studentRegisterDefence');

    Route::middleware('auth.charon.submissions.view.require') // reduce available student registration times
        ->get('charons/{charon}/labs/unavailable', 'DefenseRegistrationController@getUsedDefenceTimes');

    // CHARON TEMPLATES
    Route::middleware('auth.course_module.enrolment.require')
        ->get('charons/{charon}/templates', 'TemplatesController@get'); // get templates by id

    // DJANGO PLAGIARISM
    Route::middleware('auth.charon.managing.require')
        ->get('charons/{charon}/matches', 'PlagiarismController@fetchMatches');

    Route::middleware('auth.charon.managing.require')
        ->get('charons/{charon}/run-matches', 'PlagiarismController@fetchMatchesByRun');

    Route::middleware('auth.charon.managing.require')
        ->post('charons/{charon}/plagiarism/run', 'PlagiarismController@runCheck');

    Route::middleware('auth.charon.managing.require')
        ->get('/charons/{charon}/plagiarism-checks', 'PlagiarismController@getLatestStatus');

    Route::middleware('auth.course.managing.require')
        ->get('/courses/{course}/checks-history/', 'PlagiarismController@getCheckHistory');

    Route::middleware('auth.course.managing.require')
        ->post('courses/{course}/updateMatchStatus', 'PlagiarismController@updateMatchStatus');

    Route::middleware('auth.course.managing.require')
        ->get('courses/{course}/users/{uniid}/matches', 'PlagiarismController@fetchStudentMatches');

    // DJANGO PLAGIARISM CALLBACK
    Route::post('plagiarism_callback/{plagiarismCheck}', 'PlagiarismCallbackController@index')
        ->name('plagiarism_callback');

});

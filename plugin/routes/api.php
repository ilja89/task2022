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
        ->get('charons/{charon}/submissions', 'SubmissionsController@getByCharon');
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

    // get labs which are available for registration
    Route::get('charons/{charon}/labs/available', 'LabController@findAvailableLabsByCharon');
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
    // number of affected registrations when lab is going to be deleted or modified
    Route::middleware('auth.course.managing.require')
        ->get('courses/{course}/labs/{lab}/registrations', 'LabController@countRegistrations');
    Route::middleware('auth.charon.submissions.view.require') // get lab queue status
        ->get('charons/{charon}/defenseLab/{defenseLab}/queueStatus', 'LabController@getLabQueueStatus');

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

    Route::middleware('auth.charon.managing.require') // add registration by teacher
        ->post('charons/{charon}/submissions/register/teacher', 'DefenseRegistrationController@registerDefenceByTeacher');
    Route::middleware('auth.charon.submissions.view.require') // add registration by student
        ->post('charons/{charon}/submissions/register/student', 'DefenseRegistrationController@registerDefenceByStudent');

    Route::middleware('auth.charon.submissions.view.require') // reduce available student registration times
        ->get('charons/{charon}/labs/unavailable', 'DefenseRegistrationController@getUsedDefenceTimes');

    // CHARON TEMPLATES
    Route::middleware('auth.course_module.enrolment.require')
        ->get('charons/{charon}/templates', 'TemplatesController@get'); // get templates by id
});

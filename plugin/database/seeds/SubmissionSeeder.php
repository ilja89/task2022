<?php

use Illuminate\Database\Seeder;
use TTU\Charon\Exceptions\IncorrectSecretTokenException;
use TTU\Charon\Http\Controllers\Api\TesterCallbackController;
use TTU\Charon\Http\Requests\TesterCallbackRequest;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Models\Submission;
use TTU\Charon\Http\Controllers\Api\StudentsController;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\User;

class SubmissionSeeder extends Seeder
{
    /** @var TesterCallbackController */
    private $controller;

    /**
     * @param TesterCallbackController $controller
     * @param StudentsController $studentController
     */
    public function __construct(TesterCallbackController $controller, StudentsController $studentController) {
        $this->controller = $controller;
        $this->studentsController = $studentController;
    }
    
    /* Function needed to get only names from array with all info of students belonging to course
     * Takes -> 
     *  - Complex string array $studentList
     * Returns ->
     *  - Simple string array $nameList
     * 
     */
    public function extractNames($studentList)
    {
		$nameList = null;
		$studentList = json_decode($studentList,true);
		for($i=0;$i<count($studentList);$i++)
		{
			$nameList[$i] = $studentList[$i]["username"];
		}
		return $nameList;
	}
	
	/* Function needed to pass only users who belong to course
	 * Returns only these array elements what exist in both arrays
	 * Takes ->
	 *  - Simple string array $usernames
	 *  - Simple string array $filter
	 * Returns ->
	 *  - Simple string array $filtered
	 * 
	 */
	public function usernamesFilter($usernames,$filter)
	{
		sort($usernames);
		sort($filter);
		$filtered;
		for($i=0;$i<count($usernames);$i++)
		{
			for($c=0;$c<count($filter);$c++)
			{
				if($usernames[$i]==$filter[$c])
				{
					$filtered[] = $usernames[$i];
					break;
				}
			}
		}
		return $filtered;
	}

    /**
     * Create a Submission under a specific Charon.
     *
     * php artisan db:seed --class=SubmissionSeeder
     *
     * @return void
     * @throws IncorrectSecretTokenException
     */
    public function run()
    {
        /** @var User $user */
        $user = User::findOrFail((int) $this->command->ask('Enter User ID'));

        /** @var Charon $charon */
        $charon = Charon::findOrFail((int) $this->command->ask('Enter Charon ID'));

        $usernames = '';

        if ($this->command->confirm('Is this a group submission?')) {
            $usernames = $this->command->ask('Provide usernames for students, separated by comma');
            if (!empty($usernames)) {
                $usernames = array_map('trim', explode(',', $usernames));
            }
        }

        $testSuites = [];
        $style = 0;

        foreach ($charon->grademaps as $grademap) {
            if ($grademap->grade_type_code >= 1000) {
                continue;
            }

            $result = (int) $this->command->ask('Enter % for ' . $grademap->name, 100);

            if ($grademap->grade_type_code == 101) {
                $style = $result;
            } else {
                $testSuites[] = [
                    'name' => 'Main task',
                    'file' => 'file.exe',
                    'weight' => 1,
                    'passedCount' => 1,
                    'grade' => $result,
                    'unitTests' => []
                ];
            }
        }

        /** @var Course $course */
        $course = Course::find($charon->course);

        /** @var GitCallback $callback */
        $callback = factory(GitCallback::class)->create(['repo' => 'git@gitlab.cs.ttu.ee:username/' . $course->shortname . '.git']);

        /** @var Submission $submission */
        $submission = factory(Submission::class)->make();

        $payload = [
            'uniid' => $user->username,
            'style' => $style,
            'hash' => $submission->git_hash,
            'output' => $user->email,
            'consoleOutputs' => $submission->stdout,
            'message' => $submission->git_commit_message,
            'testSuites' => $testSuites,
            'returnExtra' => [
                'token' => $callback->secret_token,
                'charon' => $charon->id
            ]
        ];
        /* DEBUG INFO
        $this->command->comment("Usernames-> ".json_encode($usernames));
        $this->command->comment("Course-> ".json_encode($course));
        $this->command->comment("Users belonging to course-> ".json_encode($this->studentsController->searchStudents($course)));
        */
        $usernamesBelongToCourse = $this->extractNames(json_encode($this->studentsController->searchStudents($course)));
        $usernames = $this->usernamesFilter($usernames,$usernamesBelongToCourse);
        /* DEBUG INFO
        $this->command->comment("Users belonging to course names-> ".json_encode($usernamesBelongToCourse));
        $this->command->comment("Students passed through filter-> ".json_encode($usernames));
        */

        if (!empty($usernames)) {
            $payload['returnExtra']['usernames'] = $usernames;
        }

        $this->controller->index(new TesterCallbackRequest($payload));
    }
}

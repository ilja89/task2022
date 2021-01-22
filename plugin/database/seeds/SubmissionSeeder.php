<?php

use Illuminate\Database\Seeder;
use TTU\Charon\Exceptions\IncorrectSecretTokenException;
use TTU\Charon\Http\Controllers\Api\TesterCallbackController;
use TTU\Charon\Http\Requests\TesterCallbackRequest;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Models\Submission;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\User;

class SubmissionSeeder extends Seeder
{
    /** @var TesterCallbackController */
    private $controller;

    /**
     * @param TesterCallbackController $controller
     */
    public function __construct(TesterCallbackController $controller) {
        $this->controller = $controller;
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

        if (!empty($usernames)) {
            $payload['returnExtra']['usernames'] = $usernames;
        }

        $this->controller->index(new TesterCallbackRequest($payload));
    }
}

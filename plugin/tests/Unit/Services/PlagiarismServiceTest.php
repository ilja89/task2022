<?php

namespace Tests\Unit\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\Mock;
use Tests\TestCase;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Submission;
use TTU\Charon\Models\Template;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\CourseRepository;
use TTU\Charon\Repositories\PlagiarismRepository;
use TTU\Charon\Services\PlagiarismCommunicationService;
use TTU\Charon\Services\PlagiarismService;
use TTU\Charon\Services\SubmissionService;
use TTU\Charon\Services\TemplateService;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\User;
use Zeizig\Moodle\Services\UserService;

class PlagiarismServiceTest extends TestCase
{
    /** @var Mock|PlagiarismCommunicationService */
    private $plagiarismCommunicationService;

    /** @var Mock|CharonRepository */
    private $charonRepository;

    /** @var Mock|PlagiarismRepository */
    private $plagiarismRepository;

    /** @var Mock|UserService */
    private $userService;

    /** @var Mock|SubmissionService */
    private $submissionService;

    /** @var Mock|CourseRepository */
    private $courseRepository;

    /** @var Mock|TemplateService */
    private $templateService;


    /** @var PlagiarismService */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PlagiarismService(
            $this->plagiarismCommunicationService = Mockery::mock(PlagiarismCommunicationService::class),
            $this->charonRepository = Mockery::mock(CharonRepository::class),
            $this->plagiarismRepository = Mockery::mock(PlagiarismRepository::class),
            $this->userService = Mockery::mock(UserService::class),
            $this->submissionService = Mockery::mock(SubmissionService::class),
            $this->courseRepository = Mockery::mock(CourseRepository::class),
            $this->templateService = Mockery::mock(TemplateService::class)
        );
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testRunCheckCorrectlyWithoutTemplates()
    {
        $course = new Course();
        $course->id = 999;
        $course->shortname = 'iti-000000';

        $charon = new Charon();
        $charon->id = 999;
        $charon->course = $course->id;
        $charon->name = 'ex01';
        $charon->project_folder = 'folder';
        $charon->plagiarism_assignment_id = 1;

        $user = $this->getUser();

        $data = [
            'charon' => $charon->name,
            'uniid' => 'username',
            'assignment_id' => 1
        ];

        $this->userService
            ->shouldReceive('getUniidIfTaltechUsername')
            ->with($user->username)
            ->once()
            ->andReturn('username');

        $this->templateService
            ->shouldReceive('getTemplates')
            ->with($charon->id)
            ->once()
            ->andReturn([]);

        $returnData = [
            'check_finished' => false,
            'created_timestamp' => '2022-05-27 13:05:38.072535+00:00',
            'run_id' => 10,
            'status' => 'Got charon request to start check.'
        ];

        $this->plagiarismCommunicationService
            ->shouldReceive('runCheck')
            ->with($data)
            ->once()
            ->andReturn($returnData);

        $result = $this->service->runCheck($charon, $user);

        $this->assertEquals($user->firstname . ' ' . $user->lastname, $result['author']);
        $this->assertEquals($charon->name, $result['charon']);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testRunCheckCorrectlyWithTemplates()
    {

        /** @var Course $course */
        $course = factory(Course::class)->create(['shortname' => 'iti-000000']);

        /** @var Charon $charon */
        $charon = factory(Charon::class)->create([
            'course' => $course->id,
            'name' => 'ex01',
            'project_folder' => 'folder',
            'plagiarism_assignment_id' => 1
        ]);

        /** @var Template $template */
        $template = factory(Template::class)->create([
            'charon_id' => $charon->id,
            'path' => 'file.py',
            'contents' => 'template',
            'created_at' => Carbon::now()
        ]);

        /** @var Template $template2 */
        $template2 = factory(Template::class)->create([
            'charon_id' => $charon->id,
            'path' => 'file2.py',
            'contents' => 'template2',
            'created_at' => Carbon::now()
        ]);

        $user = $this->getUser();

        $data = [
            'charon' => $charon->name,
            'uniid' => 'username',
            'assignment_id' => 1
        ];

        $this->userService
            ->shouldReceive('getUniidIfTaltechUsername')
            ->with($user->username)
            ->once()
            ->andReturn('username');

        $this->templateService
            ->shouldReceive('getTemplates')
            ->with($charon->id)
            ->once()
            ->andReturn([$template, $template2]);

        $data['base_files'] = [
            [
                'file_name' => $template->path,
                'file_content' => $template->contents,
            ],
            [
                'file_name' => $template2->path,
                'file_content' => $template2->contents,
            ]
        ];

        $returnData = [
            'check_finished' => false,
            'created_timestamp' => '2022-05-27 13:05:38.072535+00:00',
            'run_id' => 10,
            'status' => 'Got charon request to start check.'
        ];

        $this->plagiarismCommunicationService
            ->shouldReceive('runCheck')
            ->with($data)
            ->once()
            ->andReturn($returnData);

        $result = $this->service->runCheck($charon, $user);

        $this->assertEquals($user->firstname . ' ' . $user->lastname, $result['author']);
        $this->assertEquals($charon->name, $result['charon']);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testRunCheckCorrectlyWithBaseFiles()
    {
        /** @var Course $course */
        $course = factory(Course::class)->create(['shortname' => 'iti-000000']);

        /** @var Charon $charon */
        $charon = factory(Charon::class)->create([
            'course' => $course->id,
            'name' => 'ex01',
            'project_folder' => 'folder',
            'allow_submission' => true,
            'plagiarism_assignment_id' => 1
        ]);

        $user = $this->getUser();

        /** @var Submission $submission */
        $submission = factory(Submission::class)->create([
            'charon_id' => $charon->id,
            'user_id' => $user->id,
            'git_timestamp' => Carbon::now()
        ]);
        $submission->user = $user;

        DB::table('charon_submission_file')->insert([
            'submission_id' => $submission->id,
            'path' => 'file.py',
            'contents' => 'base_file'
        ]);

        $data = [
            'charon' => $charon->name,
            'uniid' => 'username',
            'assignment_id' => 1
        ];

        $this->userService
            ->shouldReceive('getUniidIfTaltechUsername')
            ->with($user->username)
            ->twice()
            ->andReturn('username');

        $this->submissionService
            ->shouldReceive('getSubmissionForEachStudent')
            ->with($charon->id)
            ->once()
            ->andReturn([$submission]);

        $data['given_files'] = [
            [
                'username' => 'username',
                'name' => $user->firstname . ' ' . $user->lastname,
                'files' => [
                    [
                        'file_name' => 'file.py',
                        'file_content' => 'base_file'
                    ]
                ],
                'external_id' => $submission->id,
                'commit_hash' => $submission->git_hash
            ]
        ];

        $this->templateService
            ->shouldReceive('getTemplates')
            ->with($charon->id)
            ->once()
            ->andReturn([]);

        $returnData = [
            'check_finished' => false,
            'created_timestamp' => '2022-05-27 13:05:38.072535+00:00',
            'run_id' => 10,
            'status' => 'Got charon request to start check.'
        ];

        $this->plagiarismCommunicationService
            ->shouldReceive('runCheck')
            ->with($data)
            ->once()
            ->andReturn($returnData);

        $result = $this->service->runCheck($charon, $user);

        $this->assertEquals($user->firstname . ' ' . $user->lastname, $result['author']);
        $this->assertEquals($charon->name, $result['charon']);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetMatches()
    {
        /** @var Course $course */
        $course = factory(Course::class)->create(['shortname' => 'iti-000000']);

        /** @var Charon $charon */
        $charon = factory(Charon::class)->create([
            'course' => $course->id,
            'name' => 'ex01',
            'project_folder' => 'folder',
            'allow_submission' => true,
            'plagiarism_assignment_id' => 1
        ]);

        $timesData = [
            [
                'created_timestamp' => '2022-05-24 12:29:57.928411+00:00',
                'id' => 1
            ]
        ];

        $this->plagiarismCommunicationService
            ->shouldReceive('getMatchesHistoryTimes')
            ->with($charon->plagiarism_assignment_id)
            ->once()
            ->andReturn($timesData);

        $matchesData = $this->matchesData();

        $this->plagiarismCommunicationService
            ->shouldReceive('getMatches')
            ->with($timesData[0]['id'])
            ->once()
            ->andReturn($matchesData);

        $this->submissionService
            ->shouldReceive('findSubmissionByHash')
            ->with($matchesData[0]['commit_hash'])
            ->once();

        $this->submissionService
            ->shouldReceive('findSubmissionByHash')
            ->with($matchesData[0]['other_commit_hash'])
            ->once();

        $this->userService
            ->shouldReceive('findUserByUniid')
            ->with($matchesData[0]['uniid'])
            ->once();

        $this->userService
            ->shouldReceive('findUserByUniid')
            ->with($matchesData[0]['other_uniid'])
            ->once();

        $matchesData[0]['user_id'] = null;
        $matchesData[0]['other_user_id'] = null;
        $matchesData[0]['submission_id'] = null;
        $matchesData[0]['other_submission_id'] = null;

        $result = $this->service->getMatches($charon);

        $this->assertEquals($timesData, $result['times']);
        $this->assertEquals($matchesData, $result['matches']);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetMatchesByRun()
    {
        $run_id = 11;

        $matchesData = $this->matchesData();

        $this->plagiarismCommunicationService
            ->shouldReceive('getMatches')
            ->with($run_id)
            ->once()
            ->andReturn($matchesData);

        $this->submissionService
            ->shouldReceive('findSubmissionByHash')
            ->with($matchesData[0]['commit_hash'])
            ->once();

        $this->submissionService
            ->shouldReceive('findSubmissionByHash')
            ->with($matchesData[0]['other_commit_hash'])
            ->once();

        $this->userService
            ->shouldReceive('findUserByUniid')
            ->with($matchesData[0]['uniid'])
            ->once();

        $this->userService
            ->shouldReceive('findUserByUniid')
            ->with($matchesData[0]['other_uniid'])
            ->once();

        $matchesData[0]['user_id'] = null;
        $matchesData[0]['other_user_id'] = null;
        $matchesData[0]['submission_id'] = null;
        $matchesData[0]['other_submission_id'] = null;

        $this->service->getMatchesByRun($run_id);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetLatestStatus()
    {
        /** @var Course $course */
        $course = factory(Course::class)->create(['shortname' => 'iti-000000']);

        /** @var Charon $charon */
        $charon = factory(Charon::class)->create([
            'course' => $course->id,
            'name' => 'ex01',
            'project_folder' => 'folder',
            'allow_submission' => true,
            'plagiarism_assignment_id' => 1
        ]);

        $user = $this->getUser();

        $run_id = 11;

        $runStatusData = $this->runStatusData($user, $run_id);

        $this->plagiarismCommunicationService
            ->shouldReceive('getLatestStatusByRunId')
            ->with($run_id)
            ->once()
            ->andReturn([$runStatusData]);

        $this->userService
            ->shouldReceive('findUserByUniid')
            ->with($user->username)
            ->once()
            ->andReturn($user);

        $runStatusData['author'] = $user->firstname . " " . $user->lastname;

        $this->charonRepository
            ->shouldReceive('getCharonByPlagiarismAssignmentId')
            ->with($charon->plagiarism_assignment_id)
            ->once()
            ->andReturn($charon);

        $runStatusData['charon'] = $charon->name;

        $result = $this->service->getLatestStatus($run_id);

        unset($runStatusData['assignment_id']);

        $this->assertEquals($runStatusData, $result);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetCheckHistory()
    {
        /** @var Course $course */
        $course = factory(Course::class)->create(['shortname' => 'iti-000000']);

        /** @var Charon $charon */
        $charon = factory(Charon::class)->create([
            'course' => $course->id,
            'name' => 'ex01',
            'project_folder' => 'folder',
            'allow_submission' => true,
            'plagiarism_assignment_id' => 1
        ]);

        $user = $this->getUser();

        $run_id = 11;

        $checksData = [$this->runStatusData($user, $run_id)];

        $checks = [];
        $check = $checksData[0];

        $this->plagiarismCommunicationService
            ->shouldReceive('getChecksByCourseSlug')
            ->with($course->shortname)
            ->once()
            ->andReturn($checksData);

        $this->userService
            ->shouldReceive('findUserByUniid')
            ->with($user->username)
            ->once()
            ->andReturn($user);

        $check['author'] = $user->firstname . " " . $user->lastname;

        $this->charonRepository
            ->shouldReceive('getCharonByPlagiarismAssignmentId')
            ->with($charon->plagiarism_assignment_id)
            ->once()
            ->andReturn($charon);

        $check['charon'] = $charon->name;
        unset($check['assignment_id']);
        unset($check['check_finished']);
        $checks[] = $check;

        $result = $this->service->getCheckHistory($course);

        $this->assertEquals($checks, $result);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testUpdateMatchStatus()
    {
        $matchId = 999;
        $newStatus = 'Acceptable';
        $comment = 'Uus staatus';

        $user = $this->getUser();

        $this->userService
            ->shouldReceive('findUserById')
            ->with($user->id)
            ->once()
            ->andReturn($user);

        $newStatusData = [
            'status' => 'Acceptable'
        ];

        $this->plagiarismCommunicationService
            ->shouldReceive('updateMatchStatus')
            ->with($matchId, $newStatus, $comment, $user->username)
            ->once()
            ->andReturn($newStatusData);

        $result = $this->service->updateMatchStatus($matchId, $newStatus, $comment, $user->id);

        $this->assertEquals($newStatusData, $result);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function  testCreateOrUpdateCourseSuccessful()
    {
        /** @var Course $course */
        $course = factory(Course::class)->create(['shortname' => 'iti-000000']);

        $requestData = [
            'plagiarism_lang_type' => 'python',
            'plagiarism_gitlab_group' => 1,
            'gitlab_location_type' => 'shared',
            'plagiarism_file_extensions' => '.py',
            'plagiarism_moss_passes' =>  10,
            'plagiarism_moss_matches_shown' => 25
        ];

        $data = [
            'name' => $course->shortname,
            'charon_identifier' => $course->id,
            'language' => $requestData['plagiarism_lang_type'],
            'group_id' => $requestData['plagiarism_gitlab_group'],
            'projects_location' => $requestData['gitlab_location_type'],
            'file_extensions' => ['.py'],
            'max_passes' => $requestData['plagiarism_moss_passes'],
            'number_shown' => $requestData['plagiarism_moss_matches_shown']
        ];

        $this->plagiarismCommunicationService
            ->shouldReceive('createOrUpdateCourse')
            ->with($data)
            ->once();

        $this->service->createOrUpdateCourse($course, $requestData);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function  testCreateOrUpdateCourseUnSuccessful()
    {
        /** @var Course $course */
        $course = factory(Course::class)->create(['shortname' => 'iti-000000']);

        $requestData = [
            'plagiarism_lang_type' => 'python',
            'plagiarism_gitlab_group' => 1,
            'gitlab_location_type' => 'shared',
            'plagiarism_file_extensions' => '.py',
            'plagiarism_moss_passes' =>  10,
            'plagiarism_moss_matches_shown' => null
        ];

        $this->plagiarismCommunicationService
            ->shouldNotReceive('createOrUpdateCourse');

        $this->service->createOrUpdateCourse($course, $requestData);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testCreateOrUpdateAssignment()
    {
        /** @var Course $course */
        $course = factory(Course::class)->create(['shortname' => 'iti-000000']);

        /** @var Charon $charon */
        $charon = factory(Charon::class)->create([
            'course' => $course->id,
            'name' => 'ex01',
            'project_folder' => 'folder',
            'allow_submission' => true,
            'plagiarism_assignment_id' => 1
        ]);

        $requestData = [
            'assignment_file_extensions' => '.py,.java',
            'assignment_moss_passes' =>  10,
            'assignment_moss_matches_shown' => 13
        ];

        $data = [
            'charon' =>
                [
                    'name' => $charon->name,
                    'charon_identifier' => $charon->id,
                    'directory_path' => $charon->project_folder,
                    'file_extensions' => ['.py', '.java'],
                    'max_passes' => $requestData['assignment_moss_passes'],
                    'number_shown' => $requestData['assignment_moss_matches_shown']
                ],
            'course' =>
                [
                    'name' => 'iti-000000'
                ]
        ];

        $this->plagiarismCommunicationService
            ->shouldReceive('createOrUpdateAssignment')
            ->with($data)
            ->once()
            ->andReturn(1);

        $this->courseRepository
            ->shouldReceive('getShortnameById')
            ->with($course->id)
            ->once()
            ->andReturn($course->shortname);

        $request = new Request();
        $request->query->add($requestData);

        $result = $this->service->createOrUpdateAssignment($charon, $request);

        $this->assertEquals(1, $result);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testCreateOrUpdateAssignmentUnSuccesful()
    {
        /** @var Course $course */
        $course = factory(Course::class)->create(['shortname' => 'iti-000000']);

        /** @var Charon $charon */
        $charon = factory(Charon::class)->create([
            'course' => $course->id,
            'name' => 'ex01',
            'project_folder' => 'folder',
            'allow_submission' => true,
            'plagiarism_assignment_id' => 1
        ]);

        $requestData = [
            'assignment_file_extensions' => '.py,.java',
            'assignment_moss_passes' =>  10,
            'assignment_moss_matches_shown' => null
        ];

        $this->plagiarismCommunicationService
            ->shouldNotReceive('createOrUpdateAssignment');

        $this->courseRepository
            ->shouldNotReceive('getShortnameById');

        $request = new Request();
        $request->query->add($requestData);

        $result = $this->service->createOrUpdateAssignment($charon, $request);

        $this->assertEquals(null, $result);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetStudentActiveInactiveMatches()
    {
        /** @var Course $course */
        $course = factory(Course::class)->create(['shortname' => 'iti-000000']);

        $user = $this->getUser();

        $assignmentIds = [1,2,3];

        $this->userService
            ->shouldReceive('getUniidIfTaltechUsername')
            ->with($user->username)
            ->twice()
            ->andReturn('username');

        $this->plagiarismRepository
            ->shouldReceive('getAllPlagiarismAssignmentIds')
            ->with($course->id)
            ->twice()
            ->andReturn($assignmentIds);

        $matches = [$this->matchesData()];

        $this->plagiarismCommunicationService
            ->shouldReceive('getStudentActiveMatches')
            ->with('username', $assignmentIds)
            ->once()
            ->andReturn($matches);

        $this->plagiarismCommunicationService
            ->shouldReceive('getStudentInactiveMatches')
            ->with('username', $assignmentIds)
            ->once()
            ->andReturn($matches);

        $this->service->getStudentActiveMatches($course->id, $user->username);
        $this->service->getStudentInactiveMatches($course->id, $user->username);

    }

    private function getUser()
    {
        $user = new User();
        $user->id = 999;
        $user->username = 'username@ttu.ee';
        $user->firstname = 'user';
        $user->lastname = 'name';
        return $user;
    }

    private function runStatusData($user, $run_id)
    {
        return [
            'assignment_id' => 1,
            'author' => $user->username,
            'check_finished' => false,
            'history' => [
                [
                    'created_timestamp' => '2022-05-24 12:29:57.928411+00:00',
                    'status' => 'Check started.'
                ]
            ],
            'run_id' => $run_id,
            'status' => 'Cloning 23 repos.',
            'updated_timestamp' => '2022-05-24 12:30:57.928411+00:00'
        ];
    }


    private function matchesData()
    {
        return [
            [
                'code' => 'code',
                'comments' => [],
                'commit_hash' => 'hash',
                'created_timestamp' => '2022-05-24 12:29:57.928411+00:00',
                'gitlab_commit_at' => 'https://gitlab.cs.ttu.ee/dummy/project/blob/hash/project_folder',
                'id' => 4,
                'lines_matched' => 1,
                'other_code' => 'code',
                'other_commit_hash' => 'other_hash',
                'other_percentage' => 100,
                'other_submission' => 999,
                'other_uniid' => 'uniid2',
                'percentage' => 100,
                'similarities' => [
                    [
                        'id' => 999,
                        'lines_end' => 1,
                        'lines_start' => 1,
                        'match_id' => 4,
                        'other_lines_end' => 1,
                        'other_lines_start' => 1,
                        'other_section_size' => 1,
                        'section_size' => 1
                    ]
                ],
                'status' => 'plagiarism',
                'submission' => 998,
                'uniid' => 'uniid1'
            ]
        ];
    }
}

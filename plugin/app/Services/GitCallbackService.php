<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Events\GitCallbackReceived;
use TTU\Charon\Exceptions\IncorrectSecretTokenException;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CourseSettings;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Repositories\CourseSettingsRepository;
use TTU\Charon\Repositories\GitCallbacksRepository;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\Grouping;
use Zeizig\Moodle\Models\User;

class GitCallbackService
{
    const COMMIT_ACTION_TYPES = ['added', 'modified', 'removed'];
    const DEFAULT_EMAIL_SUFFIX = '@ttu.ee';

    /** @var GitCallbacksRepository */
    private $gitCallbacksRepository;

    /** @var CourseSettingsRepository */
    private $courseSettingsRepository;

    /**
     * GitCallbackService constructor.
     *
     * @param GitCallbacksRepository $gitCallbacksRepository
     */
    public function __construct(
        GitCallbacksRepository $gitCallbacksRepository,
        CourseSettingsRepository $courseSettingsRepository
    ) {
        $this->gitCallbacksRepository = $gitCallbacksRepository;
        $this->courseSettingsRepository = $courseSettingsRepository;
    }

    /**
     * Try to identify course from repository url
     *
     * @param string $repository
     * @return Course
     */
    public function getCourse(string $repository)
    {

        $url = str_replace('.git', '', $repository);
        $url = str_replace('https://', '', $url);
        $url = preg_replace('/\w+@/m', '', $url);
        $url = preg_replace('/^[\w+.]+[\/|:]/m', '', $url);
        $sub_domains = preg_split('/[\/|\\\\]/m', $url);
        $course_regex = '/[\w\d]+.+/m';
        $course_project_regex = '/[\w\d]+-.+/m';

        foreach ($sub_domains as $meta) {
            // Try finding COURSE
            if (preg_match($course_regex, $meta)) {
                Log::debug('Looking for course "' . $meta . '"');
                $course = Course::where('shortname', $meta)->first();
                if ($course) {
                    Log::debug("Course found!");
                    return $course;
                }
            }
            // Try to split COURSE-PROJECT
            $pos = strrpos($meta, "-");
            while ($pos !== false) {
                $course_name = substr($meta, 0, $pos);
                if (preg_match($course_project_regex, $course_name)) {
                    Log::debug('Looking for course "' . $course_name . '"');
                    $course = Course::where('shortname', $course_name)->first();
                    if ($course) {
                        Log::debug("Course found!");
                        return $course;
                    }
                }
                // Find the next place to split the rest of the name
                $pos = strrpos($course_name, "-");
            }
        }

        return null;
    }

    /**
     * Extract all the files touched by given commits
     *
     * @param array $commits
     * @return array
     */
    public function getModifiedFiles(array $commits)
    {
        if (empty($commits)) {
            return [];
        }

        return collect($commits)
            ->flatMap(function ($commit) {
                $result = [];
                foreach (self::COMMIT_ACTION_TYPES as $type) {
                    if (isset($commit[$type]) && is_array($commit[$type])) {
                        $result = array_merge($result, $commit[$type]);
                    }
                }
                return $result;
            })->unique()
            ->all();
    }

    /**
     * Find charons involved in callback by identifying project charon project_folder-s in the file path
     *
     * Treat / and \ as same for folder naming purposes
     *
     * @param array $modifiedFiles
     * @param int $courseId
     * @return Charon[]
     */
    public function findCharons(array $modifiedFiles, int $courseId)
    {
        if (empty($modifiedFiles)) {
            return [];
        }

        return Charon::where([['course', $courseId]])
            ->get()
            ->filter(function ($charon) use ($modifiedFiles) {
                foreach ($modifiedFiles as $file) {
                    $file = str_replace('\\', '/', $file);
                    $folder = str_replace('\\', '/', $charon->project_folder);
                    if (substr($file, 0, strlen($folder)) === $folder) {
                        return true;
                    }
                }
                return false;
            })->all();
    }

    /**
     * Select groups that are in chosen Charon's grouping
     *
     * @param int $groupingId
     * @param string $initialUser
     * @return array
     */
    public function getGroupUsers(int $groupingId, string $initialUser)
    {
        $grouping = Grouping::where('id', $groupingId)->first();
        if (!$grouping) {
            Log::error('Unable to find grouping by ID ' . $groupingId);
            return [];
        }

        Log::debug('Trying to get ID of user "' . $initialUser . '"');

        $initiator = User::where('username', $initialUser . self::DEFAULT_EMAIL_SUFFIX)->first();
        if (!$initiator) {
            Log::warning('Unable to find user "' . $initialUser . '"');
            return [];
        }

        Log::debug('Initiator ID is: ' . $initiator->id);

        $initiatorGroups = DB::table('groups_members')
            ->select('groupid')
            ->where('userid', $initiator->id)
            ->get()
            ->flatMap(function ($group) use ($grouping) {
                return $grouping->groups()->where('groups.id', $group->groupid)->get();
            })
            ->filter();

        if ($initiatorGroups->isEmpty()) {
            return [];
        }

        if (sizeof($initiatorGroups) > 1) {
            Log::warning(
                'Found more than one group, submitting as individual work of user "' . $initialUser . '"',
                ['groups' => $initiatorGroups]
            );
            return [$initialUser];
        }

        return $initiatorGroups->first()
            ->members()
            ->get()
            ->pluck('username')
            ->map(function ($username) {
                return str_replace(self::DEFAULT_EMAIL_SUFFIX, '', $username);
            })
            ->all();
    }

    public function saveFromCallback(string $username, string $fullUrl, string $repo, string $callbackUrl, array $params,
    array $commitFiles)
    {
        $course = $this->getCourse($repo);

        if (is_null($course)) {
            Log::warning('No course discovered, maybe git repo address is not in valid format.');
            $this->saveCallbackForUser($username, $fullUrl, $repo, $callbackUrl, $params);
            return 'NO COURSE';
        }

        Log::debug('Found course: "' . $course->shortname . '" with ID ' . $course->id);

        /** @var CourseSettings $settings */
        $settings = $this->courseSettingsRepository->getCourseSettingsByCourseId($course->id);

        $params['gitTestRepo'] = '';
        $params['testingPlatform'] = '';

        if ($settings && $settings->unittests_git) {
            Log::info("Unittests_git found from CourseSettings: '" . $settings->unittests_git . "'");
            $params['gitTestRepo'] = $settings->unittests_git;
        }

        if ($settings && $settings->testerType) {
            Log::info("TesterType found from CourseSettings: '" . $settings->testerType->name . "'");
            $params['testingPlatform'] = $settings->testerType->name;
        }

        $modifiedFiles = $this->getModifiedFiles($commitFiles);
        Log::debug('Found modified files: ', $modifiedFiles);

        $charons = $this->findCharons($modifiedFiles, $course->id);

        if (empty($charons)) {
            Log::warning('No matching Charons were found. Forwarding to tester.');
            $this->saveCallbackForUser($username, $fullUrl, $repo, $callbackUrl, $params);
            return 'NO MATCHING CHARONS';
        }

        foreach ($charons as $charon) {
            Log::debug("Found charon with id: " . $charon->id);

            $params['slugs'] = [$charon->project_folder];
            $params['testingPlatform'] = $charon->testerType->name;
            $params['systemExtra'] = explode(',', $charon->system_extra);
            $params['dockerExtra'] = $charon->tester_extra;
            $params['dockerTestRoot'] = $charon->docker_test_root;
            $params['dockerContentRoot'] = $charon->docker_content_root;
            $params['dockerTimeout'] = $charon->docker_timeout;
            $params['returnExtra'] = ['charon' => $charon->id];

            if ($charon->grouping_id == null) {
                Log::info('This charon is not a group work or is broken. Forwarding to tester.');
                $this->saveCallbackForUser($username, $fullUrl, $repo, $callbackUrl, $params);
                continue;
            }

            Log::debug('Charon has grouping id ' . $charon->grouping_id);
            $usernames = $this->getGroupUsers($charon->grouping_id, $username);

            if (empty($usernames)) {
                Log::warning('Unable to find users in group. Forwarding to tester.');
                $this->saveCallbackForUser($username, $fullUrl, $repo, $callbackUrl, $params);
                continue;
            }

            $params['returnExtra']['usernames'] = $usernames;
            $this->saveCallbackForUser($username, $fullUrl, $repo, $callbackUrl, $params);
        }

        return 'SUCCESS';
    }

    /**
     * Save callback and send event for tester
     *
     * @param string $username
     * @param string $fullUrl
     * @param string $repositoryUrl
     * @param string $callbackUrl
     * @param array $params
     */
    public function saveCallbackForUser(
        string $username,
        string $fullUrl,
        string $repositoryUrl,
        string $callbackUrl,
        array $params
    ) {
        $username = str_replace(self::DEFAULT_EMAIL_SUFFIX, '', $username);

        Log::info('Submitting work as user "' . $username . '"');

        $gitCallback = $this->gitCallbacksRepository->save(
            $fullUrl,
            $repositoryUrl,
            $username
        );

        $params['uniid'] = $username;
        $params['gitStudentRepo'] = $repositoryUrl;

        if (!isset($params['email'])) {
            $params['email'] = $username . self::DEFAULT_EMAIL_SUFFIX;
        }

        event(new GitCallbackReceived(
            $gitCallback,
            $callbackUrl,
            $params
        ));
    }

    /**
     * Check if the given token is valid and returns a Git callback.
     *
     * @param string $token
     *
     * @return GitCallback
     * @throws IncorrectSecretTokenException
     */
    public function checkGitCallbackForToken(string $token)
    {
        $gitCallback = $this->gitCallbacksRepository->findByToken($token);
        $this->checkGitCallback($gitCallback);

        return $gitCallback;
    }

    /**
     * Check the given Git callback. If the secret token isn't correct
     * throw an exception. Also set the response received flag to true.
     *
     * @param GitCallback $gitCallback
     *
     * @throws IncorrectSecretTokenException
     */
    private function checkGitCallback(GitCallback $gitCallback)
    {
        if ($gitCallback->first_response_time === null) {

            $gitCallback->first_response_time = Carbon::now();
            $gitCallback->save();
        } else if ($gitCallback->first_response_time->diffInMinutes(Carbon::now()) > 3) {
            throw new IncorrectSecretTokenException('incorrect_secret_token', $gitCallback->secret_token);
        }
    }
}

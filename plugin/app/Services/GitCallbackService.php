<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Events\GitCallbackReceived;
use TTU\Charon\Exceptions\IncorrectSecretTokenException;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\GitCallback;
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

    /**
     * GitCallbackService constructor.
     *
     * @param GitCallbacksRepository $gitCallbacksRepository
     */
    public function __construct(GitCallbacksRepository $gitCallbacksRepository)
    {
        $this->gitCallbacksRepository = $gitCallbacksRepository;
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
        $course_regex = '/[\w\d]+-.+/m';

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
                if (preg_match($course_regex, $course_name)) {
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
     * @param int $groupId
     * @param string $initialUser
     * @return array
     */
    public function getGroupUsers(int $groupId, string $initialUser)
    {
        $grouping = Grouping::where('id', $groupId)->first();
        if (!$grouping) {
            Log::error('Unable to find group by ID ' . $groupId);
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

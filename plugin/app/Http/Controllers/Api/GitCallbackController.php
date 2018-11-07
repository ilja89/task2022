<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use TTU\Charon\Events\GitCallbackReceived;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Http\Requests\GitCallbackPostRequest;
use TTU\Charon\Http\Requests\GitCallbackRequest;
use TTU\Charon\Repositories\GitCallbacksRepository;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\Group;
use Zeizig\Moodle\Models\User;
use Zeizig\Moodle\Models\Grouping;
use TTU\Charon\Models\Charon;
/**
 * Class GitCallbackController.
 * Receives Git callbacks, saves them and notifies the tester of them.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */
class GitCallbackController extends Controller
{
    /** @var GitCallbacksRepository */
    private $gitCallbacksRepository;

    /**
     * GitCallbackController constructor.
     *
     * @param  Request $request
     * @param GitCallbacksRepository $gitCallbacksRepository
     */
    public function __construct(
        Request $request,
        GitCallbacksRepository $gitCallbacksRepository
    ) {
        parent::__construct($request);
        $this->gitCallbacksRepository     = $gitCallbacksRepository;
    }

    /**
     * Handle the Git callback. Will generate a key and send it to the tester.
     * This will take all received parameters and add some and send these
     * to the tester.
     * The tester will then run tests and send the results back to Moodle.
     *
     * @param GitCallbackRequest $request
     *
     * @return string
     */
    public function index(GitCallbackRequest $request)
    {
        $gitCallback = $this->gitCallbacksRepository->save(
            $request->fullUrl(),
            $request->input('repo'),
            $request->input('user')
        );

        event(new GitCallbackReceived(
            $gitCallback,
            $request->getUriForPath('/api/tester_callback'),
            $request->all()
        ));

        return "SUCCESS";
    }

    /**
     * Handle the Git callback. Will generate a key and send it to the tester.
     * This will take all received parameters and add some and send these
     * to the tester.
     * The tester will then run tests and send the results back to Moodle.
     * This is for the new POST request.
     *
     * @param GitCallbackPostRequest $request
     *
     * @return string
     */
    public function indexPost(GitCallbackPostRequest $request)
    {
        $repo = $request->input('repository')['git_ssh_url'];
        $initial_user = $request->input('user_username');
        
        // Fetch Course name and Project folder from Git repo address
        $meta = preg_split('~-(?=[^-]*$)~', str_replace('.git', '', substr($repo, strrpos($repo, '/') + 1)));
        $project_folder = $meta[1];
        $course_name = $meta[0];

        Log::info('Discovered course name: "'.$course_name.'" and project folder: "'.$project_folder.'"');

        // Find course with specified name
        $course = Course::where('shortname', $course_name)->first();

        Log::info("Found course. It's id is " . $course->id);
        
        // Find charon
        $charon = Charon::where([
            ['project_folder', $project_folder],
            ['course', $course->id]])->first();

        Log::info("Found charon with id: " . $charon->id);

        // TODO: Trim model requests to select only required fields

        if($charon->grouping_id !== null) {
            Log::info('Charon has grouping id ' . $charon->grouping_id);
            // Get grouping
            $grouping = Grouping::where('id', $charon->grouping_id)->first();
            // Get submitter's User ID by username
            $initiator = User::where('username', $initial_user)->first()->id;
            Log::info('Initiator ID is: ' . $initiator);
            // Get groups of submitter
            $initiator_groups = DB::table('groups_members')->select('groupid')->where('userid', $initiator)->get();

            $usernames = array();
            foreach($initiator_groups as $group) {
                // Select groups that are in chosen Charon's grouping
                $grouping_group = $grouping->groups()->where('groups.id', $group->groupid)->first();
                if($grouping_group) {
                    // Fetch usernames from group
                    Log::info('Grouping group' . $grouping_group->name);
                    $members = $grouping_group->members()->get();
                    foreach($members as $member) {
                        //TODO: Verify uniqueness
                        Log::info('User: ' . $member->username);
                        array_push($usernames, $member->username);
                    }
                }
            }

            // TODO: Iterate through usernames array
            // TODO: Remove when implemented
            $username = $request->input('user_username');
        } else {
            Log::info('This charon is not a group work. Forwarding to tester.');
            $username = $request->input('user_username');
        }

        $gitCallback = $this->gitCallbacksRepository->save(
            $request->fullUrl(),
            $repo,
            $username
        );
        
        $params = ['repo' => $repo, 'user' => $username, 'extra' => $request->all()];

        event(new GitCallbackReceived(
            $gitCallback,
            $request->getUriForPath('/api/tester_callback'),
            $params
        ));

        return "SUCCESS";
    }
}

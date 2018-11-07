<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use TTU\Charon\Events\GitCallbackReceived;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Http\Requests\GitCallbackPostRequest;
use TTU\Charon\Http\Requests\GitCallbackRequest;
use TTU\Charon\Repositories\GitCallbacksRepository;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\Group;
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

        Log::info("Found course. It's id is " . $course->id . " :)");
        
        // Find charon
        $charon = Charon::where([
            ['project_folder', $project_folder],
            ['course', $course->id]])->first();

        Log::info("Found charon with id: " . $charon->id);

        if($charon->grouping_id !== null) {
            Log::info('Charon has grouping id' . $charon->grouping_id);
            // Get grouping
            $grouping = Grouping::where('id', $charon->grouping_id)->first();
            // Get groups in grouping
            $groups = $grouping->groups()->get();

            
            foreach($groups as $group) {
                $members = $group->members()->get();
                foreach($members as $member) {
                    Log::info('User in group: ' . $member->username);
                }
            }
            // TODO: select only group that this user has + iteration1
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

        //TODO: find if user is in charon's grouping
        //TODO iterate through user's group
        
        $params = ['repo' => $repo, 'user' => $username, 'extra' => $request->all()];

        event(new GitCallbackReceived(
            $gitCallback,
            $request->getUriForPath('/api/tester_callback'),
            $params
        ));

        return "SUCCESS";
    }
}

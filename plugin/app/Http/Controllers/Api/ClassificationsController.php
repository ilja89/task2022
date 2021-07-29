<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Repositories\ClassificationsRepository;

/**
 * Class CommentsController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */
class ClassificationsController extends Controller
{
    /** @var ClassificationsRepository */
    private $classificationsRepository;

    /** @var Request */
    protected $request;


    /**
     * CommentsController constructor.
     *
     * @param Request $request
     * @param ClassificationsRepository $classificationsRepository
     */
    public function __construct(Request $request, ClassificationsRepository $classificationsRepository)
    {
        parent::__construct($request);
        $this->request = $request;
        $this->classificationsRepository = $classificationsRepository;
    }

    /**
     * Get comments by the charon and student from request.
     *
     * @return \Illuminate\Database\Eloquent\Collection|\TTU\Charon\Models\Comment[]
     */
    public function getAllTesterTypes()
    {
        return $this->classificationsRepository->getAllTesterTypes();
    }

    /**
     * Saves a comment. Comment details are taken from the request.
     *
     * @param $course_id
     * @param $tester_name
     * @return array
     */
    public function saveTesterType($course_id, $tester_name)
    {
        Log::info("Saving tester with name:", [$tester_name]);
        $this->classificationsRepository->saveTesterTypes($tester_name);

        return [
            'status' => 'OK',
            'testerType' => $tester_name,
        ];
    }

    /**
     * Saves a comment. Comment details are taken from the request.
     *
     * @param $course_id
     * @param $tester_name
     * @return array
     */
    public function removeTesterType($course_id, $tester_name)
    {
        Log::info("Removing tester with name:", [$tester_name]);
        $this->classificationsRepository->removeTesterType($tester_name);

        return [
            'status' => 'OK',
            'testerType' => $tester_name,
        ];
    }

    public function getCharonTesterLanguage($course_id, $code)
    {
        $testerType = $this->classificationsRepository->getTesterTypeName($code);
        if ($testerType->name === 'javang'){
            return 'java';
        }
        return $testerType->name;
    }
}

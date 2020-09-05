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

    /**
     * CommentsController constructor.
     *
     * @param Request $request
     * @param ClassificationsRepository $classificationsRepository
     */
    public function __construct(Request $request, ClassificationsRepository $classificationsRepository)
    {
        parent::__construct($request);
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
     * @param Request $request
     * @return array
     */
    public function saveTesterType(Request $request)
    {
//        Log::error("Saving tester time", [$request]);
//        $name = $request->input('name');
//        $this->classificationsRepository->saveTesterTypes($name);

        return [
            'status' => 'OK',
            'testerType' => $request,
        ];
    }

    /**
     * Saves a comment. Comment details are taken from the request.
     *
     * @param String $name
     *
     * @return array
     */
    public function removeTesterType(Request $request)
    {
        Log::error("Removing tester time", [$request]);
        $name = $request->input('name');
        $this->classificationsRepository->removeTesterType($name);

        return [
            'status' => 'OK',
            'testerType' => $name,
        ];
    }
}

<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\TemplatesRepository;

/**
 * Class TemplatesController
 * @package TTU\Charon\Http\Controllers\Api
 */
class TemplatesController extends Controller
{

    /** @var TemplatesRepository */
    private $templatesRepository;

    /**
     * TemplatesController constructor.
     * @param Request $request
     * @param TemplatesRepository $templatesRepository
     */
    public function __construct(
        Request $request,
        TemplatesRepository $templatesRepository
    )
    {
        parent::__construct($request);
        $this->templatesRepository = $templatesRepository;
    }

    /**
     * Getting templates by charon.
     *
     * @param Charon $charon
     * @return mixed
     */
    public function get(Charon $charon)
    {
        global $CFG;
        require_once $CFG->dirroot . '/';
        $charonId = $charon->id;

        return $this->templatesRepository->getTemplates($charonId);
    }
}

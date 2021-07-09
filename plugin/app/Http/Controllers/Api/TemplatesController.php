<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\TemplatesRepository;
use TTU\Charon\Services\TemplatesService;


/**
 * Class TemplatesController
 * @package TTU\Charon\Http\Controllers\Api
 */
class TemplatesController extends Controller
{

    /** @var TemplatesService */
    private $templatesService;

    /** @var TemplatesRepository */
    private $templatesRepository;

    /** @var CharonRepository */
    private $charonRepository;

    /**
     * TemplatesController constructor.
     * @param Request $request
     * @param TemplatesService $templatesService
     * @param TemplatesRepository $templatesRepository
     * @param CharonRepository $charonRepository
     */
    public function __construct(
        Request $request,
        TemplatesService $templatesService,
        TemplatesRepository $templatesRepository,
        CharonRepository $charonRepository
    )
    {
        parent::__construct($request);
        $this->templatesRepository = $templatesRepository;
        $this->templatesService = $templatesService;
        $this->charonRepository = $charonRepository;
    }

    /**
     * Method to store templates. If path exists, then update content.
     *
     * @param Request $request
     * @param Charon $charon
     */
    public function store(Request $request, Charon $charon)
    {
        $this->templatesService->saveOrUpdateTemplates($request->toArray(), $charon->id);
    }
}
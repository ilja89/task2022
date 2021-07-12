<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Template;
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
        $charon_id = $charon->id;
        $templates = $request->toArray();

        $this->templatesService->saveOrUpdateTemplates($charon_id, $templates);
    }

    /**
     * Deletes template by path
     *
     * @param Charon $charon
     * @param Template $template
     */
    public function delete(Charon $charon, Template $template)
    {
        $charon_id = $charon->id;
        $path = $template->path;

        $this->templatesRepository->deleteTemplate($charon_id, $path);
    }
}
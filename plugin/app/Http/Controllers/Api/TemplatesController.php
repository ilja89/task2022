<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Template;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\TemplatesRepository;
use TTU\Charon\Services\TemplateService;


/**
 * Class TemplatesController
 * @package TTU\Charon\Http\Controllers\Api
 */
class TemplatesController extends Controller
{

    /** @var TemplateService */
    private $templatesService;

    /** @var TemplatesRepository */
    private $templatesRepository;

    /**
     * TemplatesController constructor.
     * @param Request $request
     * @param TemplateService $templatesService
     * @param TemplatesRepository $templatesRepository
     * @param CharonRepository $charonRepository
     */
    public function __construct(
        Request $request,
        TemplateService $templatesService,
        TemplatesRepository $templatesRepository
    )
    {
        parent::__construct($request);
        $this->templatesRepository = $templatesRepository;
        $this->templatesService = $templatesService;
    }

    /**
     * Method to add new templates.
     *
     * @param Request $request
     * @param Charon $charon
     */
    public function store(Request $request, Charon $charon)
    {
        $charon_id = $charon->id;
        $templates = $request->toArray();

        $this->templatesService->addTemplates($charon_id, $templates);

        return response()->json([
            'status' => 200,
            'data' => [
                'message' => 'Templates saved!',
            ],
        ]);
    }

    /**
     * Method to update templates contents.
     *
     * @param Request $request
     * @param Charon $charon
     */
    public function update(Request $request, Charon $charon)
    {
        $charon_id = $charon->id;
        $templates = $request->toArray();

        $this->templatesService->updateTemplates($charon_id, $templates);

        return response()->json([
            'status' => 200,
            'data' => [
                'message' => 'Templates updated!',
            ],
        ]);
    }

    /**
     * Deletes template by path.
     *
     * @param Charon $charon
     * @param Template $template
     */
    public function delete(Charon $charon, Template $template)
    {
        $charon_id = $charon->id;
        $path = $template->path;

        $this->templatesRepository->deleteTemplate($charon_id, $path);

        return response()->json([
            'status' => 200,
            'data' => [
                'message' => 'Template deleted!',
            ],
        ]);
    }

    /**
     * Getting templates by charon.
     *
     * @param Charon $charon
     * @return mixed
     */
    public function get(Charon $charon)
    {
        $charon_id = $charon->id;

        return $this->templatesRepository->getTemplates($charon_id);
    }
}
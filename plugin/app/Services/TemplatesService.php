<?php

namespace TTU\Charon\Services;

use TTU\Charon\Repositories\TemplatesRepository;

/**
 * Class SubmissionService.
 *
 * @package TTU\Charon\Services
 */
class TemplatesService
{

    /** @var TemplatesRepository */
    private $templatesRepository;

    /**
     * TemplatesService constructor.
     * @param TemplatesRepository $templatesRepository
     */
    public function __construct(
        TemplatesRepository $templatesRepository
    )
    {
        $this->templatesRepository = $templatesRepository;
    }

    /**
     * Method to add new templates and update old templates.
     *
     * @param $templates
     * @param int $charonId
     */
    public function saveOrUpdateTemplates($templates, int $charonId)
    {
        foreach ($templates as $template) {
            $this->templatesRepository->saveTemplate($charonId, $template['path'], $template['contents']);
        }
    }
}
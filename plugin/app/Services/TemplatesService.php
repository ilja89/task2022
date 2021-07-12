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
    public function saveOrUpdateTemplates(int $charonId, $templates)
    {
        $db_templates = $this->templatesRepository->getTemplates($charonId);
        foreach ($templates as $template) {
            $template_path = $template['path'];
            $changed_contents = false;
            foreach ($db_templates as $db_template){
                if ($template_path == $db_template->path){
                    $db_template->contents = $template['contents'];
                    $this->templatesRepository->updateTemplateContents($db_template);
                    $changed_contents = true;
                    break;
                }
            }
            if (!$changed_contents){
                $this->templatesRepository->saveTemplate($charonId, $template['path'], $template['contents']);
            }
        }
    }
}
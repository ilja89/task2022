<?php

namespace TTU\Charon\Services;

use TTU\Charon\Repositories\TemplatesRepository;

/**
 * Class SubmissionService.
 *
 * @package TTU\Charon\Services
 */
class TemplateService
{

    /** @var TemplatesRepository */
    private $templatesRepository;

    /**
     * TemplateService constructor.
     * @param TemplatesRepository $templatesRepository
     */
    public function __construct(
        TemplatesRepository $templatesRepository
    )
    {
        $this->templatesRepository = $templatesRepository;
    }

    /**
     * @param $templates
     * @param int $charonId
     */
    public function updateTemplates(int $charonId, $templates)
    {
        $db_templates = $this->templatesRepository->getTemplates($charonId);
        foreach ($templates as $template) {
            $template_path = $template['path'];
            foreach ($db_templates as $db_template){
                if ($template_path == $db_template->path){
                    $db_template->contents = $template['contents'];
                    $this->templatesRepository->updateTemplateContents($db_template);
                    break;
                }
            }
        }
    }

    /**
     * @param $templates
     * @param int $charonId
     */
    public function addTemplates(int $charonId, $templates)
    {
        $db_templates = $this->templatesRepository->getTemplates($charonId);
        foreach ($templates as $template) {
            $template_path = $template['path'];
            $same_path = false;
            foreach ($db_templates as $db_template){
                if ($template_path == $db_template->path){
                    $same_path = true;
                    break;
                }
            }
            if (!$same_path){
                $this->templatesRepository->saveTemplate($charonId, $template['path'], $template['contents']);
            }
        }
    }
}
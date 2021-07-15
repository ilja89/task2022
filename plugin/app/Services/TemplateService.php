<?php

namespace TTU\Charon\Services;

use TTU\Charon\Exceptions\TemplatePathException;
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
     * @throws TemplatePathException
     */
    public function updateTemplates(int $charonId, $templates)
    {
        $dbTemplates = $this->templatesRepository->getTemplates($charonId);
        foreach ($templates as $template) {
            $templatePath = $template['path'];
            $pathInDb = false;
            foreach ($dbTemplates as $dbTemplate){
                if ($templatePath == $dbTemplate->path){
                    $dbTemplate->contents = $template['contents'];
                    $pathInDb = true;
                    break;
                }
            }
            if (!$pathInDb){
                throw new TemplatePathException('template_path_not_exists', $templatePath);
            }
        }
        foreach ($dbTemplates as $dbTemplate){
            $this->templatesRepository->updateTemplateContents($dbTemplate);
        }
    }

    /**
     * @param $templates
     * @param int $charonId
     * @throws TemplatePathException
     */
    public function addTemplates(int $charonId, $templates, $dbTemplates)
    {
        foreach ($templates as $template) {
            $templatePath = $template['path'];
            foreach ($dbTemplates as $dbTemplate){
                if ($templatePath == $dbTemplate->path){
                    throw new TemplatePathException('template_path_exists', $templatePath);
                }
            }
            $secondSearch = false;
            foreach ($templates as $template2){
                if ($templatePath == $template2['path']){
                    if ($secondSearch){
                        throw new TemplatePathException('same_path', $templatePath);
                    }
                    $secondSearch = true;
                }
            }
        }
        foreach ($templates as $template){
            $this->templatesRepository->saveTemplate($charonId, $template['path'], $template['contents']);
        }
    }
}
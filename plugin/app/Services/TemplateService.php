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
    public function addTemplates(int $charonId, $templates)
    {
        foreach ($templates as $template) {
            $templatePath = $template['path'];
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
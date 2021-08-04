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
        $this->checkTemplates($templates);
        $this->templatesRepository->deleteAllTemplates($charonId);
        foreach ($templates as $template) {
            $this->templatesRepository->saveTemplate($charonId, $template['path'], $template['contents']);
        }
    }

    /**
     * @param $templates
     * @param int $charonId
     * @throws TemplatePathException
     */
    public function addTemplates(int $charonId, $templates)
    {
        $this->checkTemplates($templates);
        foreach ($templates as $template) {
            $this->templatesRepository->saveTemplate($charonId, $template['path'], $template['contents']);
        }
    }

    /**
     * Checking if given templates have path and there is no template with the same path in db.
     *
     * @param $templates
     * @throws TemplatePathException
     */
    private function checkTemplates($templates)
    {
        foreach ($templates as $template) {
            if (preg_match('/\s/', $template['path']) or empty($template['path'])) {
                throw new TemplatePathException('template_path_are_required');
            }
        }
        foreach ($templates as $template) {
            $templatePath = $template['path'];
            $secondSearch = false;
            foreach ($templates as $template2) {
                if ($templatePath == $template2['path']) {
                    if ($secondSearch) {
                        throw new TemplatePathException('same_path', $templatePath);
                    }
                    $secondSearch = true;
                }
            }
        }
    }
}

<?php

namespace TTU\Charon\Services;

use TTU\Charon\Models\Lab;
use TTU\Charon\Repositories\LabRepository;

/**
 * Class LabService
 *
 * @package TTU\Charon\Services
 */
class LabService
{
    /** var LabRepository */
    private $labRepository;

    public function __construct(
        LabRepository $labRepository
    ) {
        $this->labRepository = $labRepository;
    }

    /**
     * Get lab by its identifier.
     *
     * @param int $labId
     *
     * @return Lab
     */
    public function getLabById(int $labId): Lab
    {
        return $this->labRepository->getLabById($labId);
    }
}
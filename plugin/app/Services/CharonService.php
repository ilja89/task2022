<?php

namespace TTU\Charon\Services;

use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\CharonRepository;

/**
 * Class CharonService.
 *
 * @package TTU\Charon\Services
 */
class CharonService
{
    /** @var CharonRepository */
    private $charonRepository;

    /**
     * CharonService constructor.
     *
     * @param CharonRepository $charonRepository
     */
    public function __construct(
        CharonRepository $charonRepository
    ) {
        $this->charonRepository = $charonRepository;
    }

    /**
     * Get charon by its identifier.
     *
     * @param int $charonId
     *
     * @return Charon
     */
    public function getCharonById(int $charonId): Charon
    {
        return $this->charonRepository->getCharonById($charonId);
    }

    /**
     * Get Charons by given course's identifier. Include labs.
     *
     * @param int $courseId
     *
     * @return Charon[]
     */
    public function findCharonsByCourseId(int $courseId): array
    {
        return $this->charonRepository->findCharonsByCourse($courseId);
    }
}

<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
use TTU\Charon\Facades\MoodleConfig;
use TTU\Charon\Models\CharonTemplate;

/**
 * Class TemplatesRepository.
 *
 * @package TTU\Charon\Repositories
 */
class TemplatesRepository
{
    /** @var MoodleConfig */
    private $moodleConfig;

    /**
     * @param MoodleConfig $moodleConfig
     */
    public function __construct(MoodleConfig $moodleConfig)
    {
        $this->moodleConfig = $moodleConfig;
    }

    /**
     * @param $charonId
     * @param $path
     * @param $contents
     * @return CharonTemplate
     */
    public function saveTemplate($charonId, $path, $contents): CharonTemplate
    {
        $template = CharonTemplate::create([
            'charon_id' => $charonId,
            'path' => $path,
            'contents' => $contents,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        return $template;
    }

}
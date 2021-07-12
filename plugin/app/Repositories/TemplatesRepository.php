<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use TTU\Charon\Models\Template;

/**
 * Class TemplatesRepository.
 *
 * @package TTU\Charon\Repositories
 */
class TemplatesRepository
{
    /**
     * TemplatesRepository constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param $charonId
     * @param $path
     * @param $contents
     * @return Template
     */
    public function saveTemplate($charonId, $path, $contents): Template
    {
        $template = Template::create([
            'charon_id' => $charonId,
            'path' => $path,
            'contents' => $contents,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        return $template;
    }

    /**
     * @param $charonId
     * @param $path
     * @return mixed
     */
    public function deleteTemplate($charonId, $path)
    {
        return DB::table('charon_code_editor_sample')
            ->where('charon_id', $charonId)
            ->where('path', $path)
            ->delete();
    }

    /**
     * @param $charonId
     * @return mixed
     */
    public function getTemplates($charonId)
    {
        return DB::table('charon_code_editor_sample')
            ->where('charon_id', $charonId)
            ->get();
    }
}
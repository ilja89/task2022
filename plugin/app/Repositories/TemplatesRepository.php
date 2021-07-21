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
     * @param int $charonId
     * @param string $path
     * @param string $contents
     * @return Template
     */
    public function saveTemplate(int $charonId, string $path, $contents): Template
    {
        return Template::create([
            'charon_id' => $charonId,
            'path' => $path,
            'contents' => $contents ?: "",
            'created_at' => Carbon::now()
        ]);
    }

    /**
     * @param int $charonId
     * @param string $path
     * @return mixed
     */
    public function deleteTemplate(int $charonId, string $path)
    {
        return DB::table('charon_template')
            ->where('charon_id', $charonId)
            ->where('path', $path)
            ->delete();
    }

    /**
     * @param int $charonId
     * @return Template[]
     */
    public function getTemplates(int $charonId): iterable
    {
        return DB::table('charon_template')
            ->where('charon_id', $charonId)
            ->get();
    }

    /**
     * @param $template
     * @return mixed
     */
    public function updateTemplateContents($template)
    {
        return DB::table('charon_template')
            ->where('charon_id', $template->charon_id)
            ->where('path', $template->path)
            ->update(['contents' => $template->contents]);
    }
}
<?php

namespace TTU\Charon\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SplFileObject;

/**
 * Class LogService.
 *
 * @package TTU\Charon\Services
 */
class LogParseService
{
    private static $LOG_ENTRY_START = "^\[\d+-\d+-\d+\s\d+:\d+:\d+\]\s+^";

    /**
     * Read the latest log entries up to app.log_display_lines limit.
     * Groups together multiline log entries, logs are sorted by latest first.
     *
     * @return String as json encoded array
     */
    public function readLogs($queryLogs = false)
    {
        if ($queryLogs) {
            $files = $this->getQueryLogFiles();
        } else {
            $files = $this->getFiles();
        }
        $limit = Config::get('app.log_display_lines');
        $allLogs = [];

        foreach ($files as $file) {
            if ($limit < 0) {
                break;
            }

            $file = $this->movePointer($file, $limit);
            $currentFileLogs = [];
            $currentEntry = [];

            while (!$file->eof() && $limit >= 0) {
                $line = trim($file->current());
                $file->next();
                $limit--;

                if (strlen($line) == 0) {
                    continue;
                }

                $startOfLine = preg_match(self::$LOG_ENTRY_START, $line);

                if (count($currentEntry) > 0 && $startOfLine) {
                    array_unshift($currentFileLogs , $currentEntry);
                    $currentEntry = [ $line ];
                } else {
                    $currentEntry[] = $line;
                }
            }

            if (count($currentEntry) > 0) {
                array_unshift($currentFileLogs , $currentEntry);
            }

            $allLogs = array_merge($allLogs, $currentFileLogs);
        }
        return json_encode($allLogs, JSON_PRETTY_PRINT);
    }

    /**
     * Pick up laravel log files sorted by the most recent first.
     *
     * @return array of log files
     */
    private function getFiles()
    {
        $files = Storage::disk('logs')->listContents('./');

        $logFiles = array_filter($files, function ($file) {
            return $file['extension'] === 'log' && substr($file['basename'], 0, 8) === 'laravel-';
        });

        usort($logFiles, function($a, $b) {
            return strcmp($b['basename'], $a['basename']);
        });

        return $logFiles;
    }

    /**
     * Fetch laravel query log files sorted by the most recent first.
     *
     * @return array of log files
     */
    private function getQueryLogFiles()
    {
        $files = Storage::disk('logs')->listContents('./');

        return array_filter($files, function($file) {
           return $file['extension'] === 'log' && $file['filename'] === 'dbQueries-' . date("Y-m-d");
        });
    }

    /**
     * Move the file pointer to the end minus the limit to get the latest log entries.
     *
     * @param $file array containing file info
     * @param $limit int maximum number of rows for this file
     * @return SplFileObject
     */
    private function movePointer($file, $limit)
    {
        $path = Storage::disk('logs')->path($file['basename']);

        $file = new SplFileObject($path);
        $file->seek(PHP_INT_MAX);
        $total_lines = $file->key();

        if ($total_lines > $limit) {
            $file->seek($total_lines - $limit);
        } else {
            $file->rewind();
        }

        return $file;
    }
}

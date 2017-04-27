<?php

namespace TTU\Charon\Foundation;

/**
 * Class Application which extends the Laravel Foundation Application class..
 * This is mainly used to redefine changed paths for the Laravel directories.
 * Eg. /plugin/app from /app etc.
 *
 * @package TTU\Charon\Foundation
 */
class Application extends \Illuminate\Foundation\Application
{

    /**
     * Returns the plugin directory path with a trailing '/'.
     *
     * @return string
     */
    private function getPluginDirectory()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'plugin' . DIRECTORY_SEPARATOR;
    }

    /**
     * Redefine the 'app' folder path.
     *
     * @param string $path
     *
     * @return string
     */
    public function path($path = '')
    {
        return $this->getPluginDirectory() . 'app' . ($path ? DIRECTORY_SEPARATOR.$path : $path);
    }


    /**
     * Redefine the 'public' folder path.
     *
     * @return string
     */
    public function publicPath()
    {
        return $this->getPluginDirectory() . 'public';
    }

    /**
     * Redefine the 'lang' folder path.
     *
     * @return string
     */
    public function langPath()
    {
        return $this->getPluginDirectory() . 'resources' . DIRECTORY_SEPARATOR . 'lang';
    }

    /**
     * Redefine the 'config' folder path.
     *
     * @param string $path
     *
     * @return string
     */
    public function configPath($path = '')
    {
        return $this->getPluginDirectory() . 'config'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Redefine the 'storage' folder path.
     *
     * @return string
     */
    public function storagePath()
    {
        return $this->getPluginDirectory() . 'storage';
    }

    /**
     * Redefine the 'database' folder path.
     *
     * @param string $path
     *
     * @return string
     */
    public function databasePath($path = '')
    {
        return $this->getPluginDirectory() . 'database'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Redefine the 'resource' folder path.
     *
     * @param string $path
     *
     * @return string
     */
    public function resourcePath($path = '')
    {
        return $this->getPluginDirectory() . 'resources'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Redefine the 'bootstrap' folder path.
     *
     * @param string $path
     *
     * @return string
     */
    public function bootstrapPath($path = '')
    {
        return $this->getPluginDirectory() . 'bootstrap'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

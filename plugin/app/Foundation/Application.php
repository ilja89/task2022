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
     * @return string
     */
    public function path()
    {
        return $this->getPluginDirectory() . 'app';
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
     * @return string
     */
    public function configPath()
    {
        return $this->getPluginDirectory() . 'config';
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
     * @return string
     */
    public function databasePath()
    {
        return $this->getPluginDirectory() . 'database';
    }

    /**
     * Redefine the 'resource' folder path.
     *
     * @return string
     */
    public function resourcePath()
    {
        return $this->getPluginDirectory() . 'resources';
    }

    /**
     * Redefine the 'bootstrap' folder path.
     *
     * @return string
     */
    public function bootstrapPath()
    {
        return $this->getPluginDirectory() . 'bootstrap';
    }
}

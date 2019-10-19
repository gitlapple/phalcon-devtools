<?php

/**
 * This file is part of the Phalcon Developer Tools.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Phalcon\Builder;

use Phalcon\Config;
use Phalcon\Config\Adapter\Ini as ConfigIni;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Path Class
 *
 * @package Phalcon\Builder
 */
class Path
{
    protected $rootPath = null;

    public function __construct($rootPath = null)
    {
        $this->rootPath = $rootPath ?: realpath('.') . DIRECTORY_SEPARATOR;
    }

    /**
     * Tries to find the current configuration in the application
     *
     * @param string $type Config type: ini | php
     * @return \Phalcon\Config
     * @throws BuilderException
     */
    public function getConfig($type = null)
    {
        $types = ['php' => true, 'ini' => true];
        $type  = isset($types[$type]) ? $type : 'ini';

        foreach (['app/config/', 'config/', 'apps/config/', 'apps/frontend/config/'] as $configPath) {
            if ('ini' == $type && file_exists($this->rootPath . $configPath . 'config.ini')) {
                return new ConfigIni($this->rootPath . $configPath . 'config.ini');
            } else {
                if (file_exists($this->rootPath . $configPath. 'config.php')) {
                    $config = include($this->rootPath . $configPath . 'config.php');
                    if (is_array($config)) {
                        $config = new Config($config);
                    }

                    return $config;
                }
            }
        }

        $directory = new RecursiveDirectoryIterator('.');
        $iterator = new RecursiveIteratorIterator($directory);
        foreach ($iterator as $f) {
            if (preg_match('/config\.php$/i', $f->getPathName())) {
                $config = include $f->getPathName();
                if (is_array($config)) {
                    $config = new Config($config);
                }

                return $config;
            } else {
                if (preg_match('/config\.ini$/i', $f->getPathName())) {
                    return new ConfigIni($f->getPathName());
                }
            }
        }

        throw new BuilderException("Builder can't locate the configuration file");
    }

    public function setRootPath($path)
    {
        $this->rootPath = rtrim(str_replace('/', DIRECTORY_SEPARATOR, $path), '\\/') . DIRECTORY_SEPARATOR;

        return $this;
    }

    public function getRootPath($path = null)
    {
        return $this->rootPath . ($path ? trim($path, '\\/') . DIRECTORY_SEPARATOR : '');
    }

    public function appendRootPath($pathPath)
    {
        $this->setRootPath($this->getRootPath() . rtrim($pathPath, '\\/') . DIRECTORY_SEPARATOR);
    }


    /**
     * Check if a path is absolute
     *
     * @param string $path Path to check
     *
     * @return bool
     */
    public function isAbsolutePath($path)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            if (preg_match('/^[A-Z]:\\\\/', $path)) {
                return true;
            }
        } else {
            if (substr($path, 0, 1) == DIRECTORY_SEPARATOR) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check Phalcon system dir
     *
     * @return bool
     */
    public function hasPhalconDir()
    {
        return file_exists($this->rootPath . '.phalcon');
    }
}

<?php

/**
 * This file is part of the Phalcon Developer Tools.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Phalcon\Utils;

use Phalcon\Devtools\Version;
use Phalcon\Di\Injectable;
use Phalcon\Version as PhVersion;

/**
 * \Phalcon\Utils\SystemInfo
 *
 * @property \Phalcon\Registry $registry
 * @property \Phalcon\Url|\Phalcon\Url\UrlInterface $url
 *
 * @package Phalcon\Utils
 */
class SystemInfo extends Injectable
{
    public function get()
    {
        return $this->getVersions() + $this->getUris() + $this->getDirectories() + $this->getEnvironment();
    }

    public function getDirectories()
    {
        return [
            'DevTools Path' => $this->registry->offsetGet('directories')->ptoolsPath,
            'Templates Path' => $this->registry->offsetGet('directories')->templatesPath,
            'Application Path' => $this->registry->offsetGet('directories')->basePath,
            'Controllers Path' => $this->registry->offsetGet('directories')->controllersDir,
            'Models Path' => $this->registry->offsetGet('directories')->modelsDir,
            'Migrations Path' => $this->registry->offsetGet('directories')->migrationsDir,
            'WebTools Views' => $this->registry->offsetGet('directories')->webToolsViews,
            'WebTools Resources' => $this->registry->offsetGet('directories')->resourcesDir,
            'WebTools Elements' => $this->registry->offsetGet('directories')->elementsDir,
        ];
    }

    public function getUris()
    {
        return [
            'Base URI' => $this->url->getBaseUri(),
            'WebTools URI' => rtrim('/', $this->url->getBaseUri()) . '/webtools.php',
        ];
    }

    public function getVersions()
    {
        return [
            'Phalcon DevTools Version' => Version::get(),
            'Phalcon Version' => PhVersion::get(),
            'AdminLTE Version' => ADMIN_LTE_VERSION,
        ];
    }

    public function getEnvironment()
    {
        return [
            'OS' => php_uname(),
            'PHP Version' => PHP_VERSION,
            'PHP SAPI' => php_sapi_name(),
            'PHP Bin' => PHP_BINARY,
            'PHP Extension Dir' => PHP_EXTENSION_DIR,
            'PHP Bin Dir' => PHP_BINDIR,
            'Loaded PHP config' => php_ini_loaded_file(),
        ];
    }
}

<?php

/**
 * This file is part of the Phalcon Developer Tools.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Phalcon\Commands\Builtin;

use Phalcon\Script\Color;
use Phalcon\Commands\Command;
use Phalcon\Utils\SystemInfo;

/**
 * Info Command
 *
 * @package Phalcon\Commands\Builtin
 */
class Info extends Command
{
    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getPossibleParams()
    {
        return [
            'help' => 'Shows this help [optional]',
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @param array $parameters
     * @return mixed
     */
    public function run(array $parameters)
    {
        $info = new SystemInfo();

        printf("%s\n", Color::head('Environment:'));
        foreach ($info->getEnvironment() as $k => $v) {
            printf("  %s: %s\n", $k, $v);
        }

        printf("%s\n", Color::head('Versions:'));
        foreach ($info->getVersions() as $k => $v) {
            printf("  %s: %s\n", $k, $v);
        }

        print PHP_EOL;

        return 0;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getCommands()
    {
        return ['info', 'i'];
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    public function canBeExternal()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function getHelp()
    {
        print Color::head('Help:') . PHP_EOL;
        print Color::colorize('  Shows versions and environment configuration') . PHP_EOL . PHP_EOL;
    }

    /**
     * {@inheritdoc}
     *
     * @return integer
     */
    public function getRequiredParams()
    {
        return 0;
    }
}

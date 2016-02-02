<?php

/*
 * This file is part of the OXID Console package.
 *
 * (c) Eligijus Vitkauskas <eligijusvitkauskas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ellis\Oxid\Console;

use Symfony\Component\Console\Application as BaseApplication;

/**
 * Extension of default Symfony Console application.
 */
class Application extends BaseApplication
{
    const VERSION = '1.3.0-DEV';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct('OXID Console', static::VERSION);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new Backport\CommandAdapter(new \CacheClearCommand());
        $commands[] = new Backport\CommandAdapter(new \DatabaseUpdateCommand());
        $commands[] = new Backport\CommandAdapter(new \FixStatesCommand());
        $commands[] = new Backport\CommandAdapter(new \GenerateMigrationCommand());
        $commands[] = new Backport\CommandAdapter(new \GenerateModuleCommand());
        $commands[] = new Backport\CommandAdapter(new \MigrateCommand());

        return $commands;
    }
}

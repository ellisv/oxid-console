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

        $this->loadCoreCommands();
        $this->loadModuleCommands();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new Command\CacheClearCommand();
        $commands[] = new Command\DatabaseUpdateCommand();
        $commands[] = new Command\MigrateCommand();
        $commands[] = new Command\GenerateMigrationCommand();

        // TODO: Port commands to a new api.
        $commands[] = new Backport\CommandAdapter(new \FixStatesCommand());
        $commands[] = new Backport\CommandAdapter(new \GenerateModuleCommand());

        return $commands;
    }

    private function loadCoreCommands()
    {
        $directory = OX_BASE_PATH . 'application/commands';
        $this->loadCommandsFromDirectory($directory);
    }

    private function loadModuleCommands()
    {
        $config = \oxRegistry::getConfig();
        $moduleDir = $config->getModulesDir();
        $modulePaths = $config->getConfigParam('aModulePaths');

        if (!is_array($modulePaths)) {
            return;
        }

        foreach ($modulePaths as $modulePath) {
            $commandDir = $moduleDir . $modulePath . '/commands';
            $this->loadCommandsFromDirectory($commandDir);
        }
    }

    private function loadCommandsFromDirectory($directory)
    {
        if (!is_dir($directory)) {
            return;
        }

        $directoryIterator = new \RecursiveDirectoryIterator($directory);
        $iterator = new \RecursiveIteratorIterator($directoryIterator);
        $files = new \RegexIterator($iterator, '/.*command\.php$/');

        foreach ($files as $file) {
            require_once $file;

            $class = substr(basename($file), 0, -4);
            $command = new $class();

            if ($command instanceof \oxConsoleCommand) {
                $command = new Backport\CommandAdapter($command);
            }

            $this->add($command);
        }
    }
}

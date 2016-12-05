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

use Ellis\Oxid\Console\Util\PathUtil;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Extension of default Symfony Console application.
 */
class Application extends BaseApplication
{
    const VERSION = '1.3.0-DEV';

    /**
     * @var array A list of deprecation warnings to render.
     */
    private $deprecations = array();

    public function __construct()
    {
        parent::__construct('OXID Console', static::VERSION);

        $this->loadCoreCommands();
        $this->loadModuleCommands();
    }

    /**
     * {@inheritdoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        foreach ($this->deprecations as $deprecation) {
            $io->note($deprecation);
        }

        parent::doRun($input, $output);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new Command\CacheClearCommand();
        $commands[] = new Command\DatabaseUpdateCommand();
        $commands[] = new Command\FixStatesCommand();
        $commands[] = new Command\GenerateMigrationCommand();
        $commands[] = new Command\GenerateModuleCommand();
        $commands[] = new Command\MigrateCommand();

        return $commands;
    }

    /**
     * Load core commands which are placed at application/commands directory.
     *
     * This was a directory for core commands at v1.2 and earlier versions of
     * oxid console. So some users might have already put their commands in
     * there.
     *
     * So we show deprecation notices that core commands which were shipped
     * with oxid console are no longer loadable and should be removed and load
     * their custom commands.
     */
    private function loadCoreCommands()
    {
        $directory = PathUtil::join(OX_BASE_PATH, 'application', 'commands');
        $this->loadCommandsFromDirectory($directory, array(
            'cacheclearcommand',
            'databaseupdatecommand',
            'fixstatescommand',
            'generatemigrationcommand',
            'generatemodulecommand',
            'listcommand',
            'migratecommand',
        ));
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

    /**
     * @param string   $directory
     * @param string[] $deprecated A list of classes which are deprecated
     */
    private function loadCommandsFromDirectory($directory, array $deprecated = array())
    {
        if (!is_dir($directory)) {
            return;
        }

        $directoryIterator = new \RecursiveDirectoryIterator($directory);
        $iterator = new \RecursiveIteratorIterator($directoryIterator);
        $files = new \RegexIterator($iterator, '/.*command\.php$/');

        foreach ($files as $file) {
            $class = substr(basename($file), 0, -4);
            if (in_array(strtolower($class), $deprecated)) {
                $this->deprecations[] = sprintf('"%s" has not been loaded to console application due deprecation.'
                                        . ' Please see upgrade guide on how to deal with this.', $file);
                continue;
            }

            require_once $file;

            $command = new $class();

            if ($command instanceof \oxConsoleCommand) {
                $this->deprecations[] = sprintf('"%s" is using old Command API. Please see upgrade guide on how to'
                                                . ' deal with this.', $file);
                $command = new Backport\CommandAdapter($command);
            }

            $this->add($command);
        }
    }
}

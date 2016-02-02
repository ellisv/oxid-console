<?php

/*
 * This file is part of the OXID Console package.
 *
 * (c) Eligijus Vitkauskas <eligijusvitkauskas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ellis\Oxid\Console\Backport;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A command adapter to able to run commands implementing oxConsoleCommand.
 *
 * oxConsoleCommand was an interface for commands which were written for 1.2 and
 * earlier releases of OXID Console. As some people might have had written their
 * own custom commands we want to support those and show deprecetion warnings
 * to have easier migration path.
 */
class CommandAdapter extends Command
{
    /**
     * @var \oxConsoleCommand
     */
    private $command;

    /**
     * Constructor.
     *
     * @param \oxConsoleCommand $command
     */
    public function __construct(\oxConsoleCommand $command)
    {
        $this->command = $command;

        parent::__construct();
    }

    /**
     * Configure a command based on oxConsoleCommand settings.
     */
    public function configure()
    {
        $this
            ->setName($this->command->getName())
            ->setDescription($this->command->getDescription());

        $memoryOutput = new MemoryOutput();
        $this->command->help($memoryOutput);
        $this->setHelp($memoryOutput->dump());
    }

    /**
     * Port an execution of command to legacy oxConsoleCommand.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO: Implement this
        //$inputAdapter = new InputAdapter($input);
        //$outputAdapter = new OutputAdapter($output);

        //return parent::execute($inputAdapter, $outputAdapter);
    }
}

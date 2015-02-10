<?php

/*
 * This file is part of the OXID Console package.
 *
 * (c) Eligijus Vitkauskas <eligijusvitkauskas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * List command
 *
 * Display all available commands in console application
 */
class ListCommand extends oxConsoleCommand
{

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('list');
        $this->setDescription('List of all available commands');
    }

    /**
     * {@inheritdoc}
     */
    public function help(oxIOutput $oOutput)
    {
        $this->execute($oOutput);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(oxIOutput $oOutput)
    {
        $aCommands = $this->getConsoleApplication()
            ->getLoadedCommands();

        $oOutput->writeLn('OXID Shop console');
        $oOutput->writeLn();
        $oOutput->writeLn('Available commands:');

        $iOffset = max(array_map('strlen', array_keys($aCommands))) + 2;

        foreach ($aCommands as $oCommand) {
            $sName = $oCommand->getName();
            $sDescription = $oCommand->getDescription();
            $iDescriptionOffset = $iOffset - strlen($sName);

            $oOutput->writeLn(sprintf("  %s %{$iDescriptionOffset}s # %s", $sName, ' ', $sDescription));
        }
    }
}

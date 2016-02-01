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
 * Migrate command
 *
 * Runs migration handler with input timestamp. If no timestamp were passed
 * runs with current timestamp instead
 */
class MigrateCommand extends oxConsoleCommand
{

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('migrate');
        $this->setDescription('Run migration scripts');
    }

    /**
     * {@inheritdoc}
     */
    public function help(oxIOutput $oOutput)
    {
        $oOutput->writeLn('Usage: migrate [<timestamp>]');
        $oOutput->writeLn();
        $oOutput->writeLn('This command runs migration scripts for given timestamp');
        $oOutput->writeLn('If no timestamp is passed than it assumes timestamp is current time');
        $oOutput->writeLn();
        $oOutput->writeLn('Available options:');
        $oOutput->writeLn('  -n, --no-debug    No debug output');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(oxIOutput $oOutput)
    {
        try {
            $sTimestamp = $this->_parseTimestamp();
        } catch (oxConsoleException $oEx) {
            $oOutput->writeLn($oEx->getMessage());
            return;
        }

        $oOutput->writeLn('Running migration scripts');

        $oInput = $this->getInput();
        $oDebugOutput = $oInput->hasOption(array('n', 'no-debug'))
            ? new oxNullOutput()
            : $oOutput;

        $oMigrationHandler = new oxMigrationHandler();
        $oMigrationHandler->run($sTimestamp, $oDebugOutput);

        $oOutput->writeLn('Migration finished successfully');
    }

    /**
     * Parse timestamp from user input
     *
     * @return string
     *
     * @throws oxConsoleException
     */
    protected function _parseTimestamp()
    {
        $oInput = $this->getInput();

        if ($sTimestamp = $oInput->getArgument(1)) {

            if (!oxMigrationQuery::isValidTimestamp($sTimestamp)) {

                if ($sTime = strtotime($sTimestamp)) {
                    $sTimestamp = date('YmdHis', $sTime);
                } else {
                    throw new oxConsoleException('Invalid timestamp format, use YYYYMMDDhhmmss format');
                }
            }

            return $sTimestamp;
        }

        return oxMigrationQuery::getCurrentTimestamp();
    }
}

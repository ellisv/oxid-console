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
 * Database update command
 *
 * Updates OXID database views
 */
class DatabaseUpdateCommand extends oxConsoleCommand
{

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('db:update');
        $this->setDescription('Updates database views');
    }

    /**
     * {@inheritdoc}
     */
    public function help(oxIOutput $oOutput)
    {
        $oOutput->writeLn('Usage: db:update');
        $oOutput->writeLn();
        $oOutput->writeLn('Updates OXID shop database views');
        $oOutput->writeLn();
        $oOutput->writeLn('If there are some changes in database schema it is always a good');
        $oOutput->writeLn('idea to run database update command');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(oxIOutput $oOutput)
    {
        $oOutput->writeLn('Updating database views');
        $config = oxRegistry::getConfig();
        //avoid problems if views are already broken
        $config->setConfigParam('blSkipViewUsage',true);

        /** @var oxDbMetaDataHandler $oDbHandler */
        $oDbHandler = oxNew('oxDbMetaDataHandler');

        if (!$oDbHandler->updateViews()) {
            $oOutput->writeLn('[ERROR] Could not update database views');
            return;
        }

        $oOutput->writeLn('Database views updated successfully');
    }
}

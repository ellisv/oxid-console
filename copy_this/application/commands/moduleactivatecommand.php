<?php
/**
 * This file is part of OXID Console.
 *
 * OXID Console is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID Console is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID Console.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author        OXID Professional services
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2015
 */


/**
 * Class ModuleActivateCommand
 */
class ModuleActivateCommand extends oxConsoleCommand
{

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('module:activate');
        $this->setDescription('Activate module in shop');
    }

    /**
     * {@inheritdoc}
     */
    public function help(oxIOutput $oOutput)
    {
        $oOutput->writeLn('Usage: module:activate [options] moduleid');
        $oOutput->writeLn();
        $oOutput->writeLn('This command imports shop config');
        $oOutput->writeLn();
        $oOutput->writeLn('Available options:');
        $oOutput->writeLn('  -n, --no-debug     No debug output');
        $oOutput->writeLn('  -s, --shop         Shop');
    }

    /**
     * Execute current command
     *
     * @param oxIOutput $oOutput
     */
    public function execute(oxIOutput $oOutput)
    {
        $oInput = $this->getInput();

        $sModuleId = $oInput->getArgument(1);

        if (!$sModuleId) {
            $this->help($oOutput);

            return;
        }

        if ($oInput->hasOption(array('s', 'shop'))) {
            $sShopId = $oInput->getOption(array('s', 'shop'));
        }

        $this->activateModule($sModuleId, $sShopId, $oOutput);
    }


    public function activateModule($sModuleId, $sShopId, $oOutput)
    {
        /** @var oxModuleInstaller $oModuleInstaller */
        $oModuleInstaller = oxRegistry::get('oxModuleInstaller');

        if ($sShopId) {
            $oConfig = oxSpecificShopConfig::get($sShopId);
            $oModuleInstaller->setConfig($oConfig);
        }

        $oxModuleList = oxNew('oxModuleList');
        $oxModuleList->getModulesFromDir(oxRegistry::getConfig()->getModulesDir());
        $aModules = $oxModuleList->getList();

        /** @var oxModule $oModule */

        $oModule = $aModules[$sModuleId];
        if ($oModule == null) {
            $oOutput->writeLn("$sModuleId not found. choose from:");
            $oOutput->writeLn(join("\n", array_keys($aModules)));

            return;
        }

        if ($oModule->isActive()) {
            $oOutput->writeLn("$sModuleId already active");
        } else {
            $oModuleInstaller->activate($oModule);
        }
    }
}

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
 * Fix States command
 */
class FixStatesCommand extends oxConsoleCommand
{

    /**
     * @var array|null Available module ids
     */
    protected $_aAvailableModuleIds = null;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('fix:states');
        $this->setDescription('Fixes modules metadata states');
    }

    /**
     * {@inheritdoc}
     */
    public function help(oxIOutput $oOutput)
    {
        $oOutput->writeLn('Usage: fix:states [options] <module_id> [<other_module_id>...]');
        $oOutput->writeLn();
        $oOutput->writeLn('This command fixes information stored in database of modules');
        $oOutput->writeln();
        $oOutput->writeLn('Available options:');
        $oOutput->writeLn('  -a, --all         Passes all modules');
        $oOutput->writeLn('  -b, --base-shop   Fix only on base shop');
        $oOutput->writeLn('  --shop=<shop_id>  Specifies in which shop to fix states');
        $oOutput->writeLn('  -n, --no-debug    No debug output');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(oxIOutput $oOutput)
    {
        $oInput = $this->getInput();
        $oDebugOutput = $oInput->hasOption(array('n', 'no-debug'))
            ? oxNew('oxNullOutput')
            : $oOutput;

        try {
            $aModuleIds = $this->_parseModuleIds();
            $aShopConfigs = $this->_parseShopConfigs();
        } catch (oxInputException $oEx) {
            $oOutput->writeLn($oEx->getMessage());
            return;
        }

        /** @var oxModuleStateFixer $oModuleStateFixer */
        $oModuleStateFixer = oxRegistry::get('oxModuleStateFixer');

        /** @var oxModule $oModule */
        $oModule = oxNew('oxModule');

        foreach ($aShopConfigs as $oConfig) {

            $oDebugOutput->writeLn('[DEBUG] Working on shop id ' . $oConfig->getShopId());

            foreach ($aModuleIds as $sModuleId) {
                if (!$oModule->load($sModuleId)) {
                    $oDebugOutput->writeLn("[DEBUG] {$sModuleId} does not exist - skipping");
                    continue;
                }

                $oDebugOutput->writeLn("[DEBUG] Fixing {$sModuleId} module");

                $oConfig->fixWrongConfigParams($sModuleId);

                $oModuleStateFixer->fix($oModule, $oConfig);
            }

            $oDebugOutput->writeLn();
        }

        $oOutput->writeLn('Fixed module states successfully');
    }

    /**
     * Parse and return module ids from input
     *
     * @return array
     *
     * @throws oxInputException
     */
    protected function _parseModuleIds()
    {
        $oInput = $this->getInput();

        if ($oInput->hasOption(array('a', 'all'))) {
            return $this->_getAvailableModuleIds();
        }

        if (count($oInput->getArguments()) < 2) { // Note: first argument is command name
            /** @var oxInputException $oEx */
            $oEx = oxNew('oxInputException');
            $oEx->setMessage('Please specify at least one module if as argument or use --all (-a) option');
            throw $oEx;
        }

        $aModuleIds = $oInput->getArguments();
        array_shift($aModuleIds); // Getting rid of command name argument

        $aAvailableModuleIds = $this->_getAvailableModuleIds();

        // Checking if all provided module ids exist
        foreach ($aModuleIds as $sModuleId) {

            if (!in_array($sModuleId, $aAvailableModuleIds)) {
                /** @var oxInputException $oEx */
                $oEx = oxNew('oxInputException');
                $oEx->setMessage("{$sModuleId} module does not exist");
                throw $oEx;
            }
        }

        return $aModuleIds;
    }

    /**
     * Parse and return shop config objects from input
     *
     * @return oxSpecificShopConfig[]
     *
     * @throws oxInputException
     */
    protected function _parseShopConfigs()
    {
        $oInput = $this->getInput();

        if ($oInput->hasOption(array('b', 'base-shop'))) {
            return array(oxRegistry::getConfig());
        }

        if ($mShopId = $oInput->getOption('shop')) {

            if (is_bool($mShopId)) { // No value for option were passed
                /** @var oxInputException $oEx */
                $oEx = oxNew('oxInputException');
                $oEx->setMessage('Please specify shop id in option following this format --shop=<shop_id>');
                throw $oEx;
            }

            if ($oConfig = oxSpecificShopConfig::get($mShopId)) {
                return array($oConfig);
            }

            /** @var oxInputException $oEx */
            $oEx = oxNew('oxInputException');
            $oEx->setMessage('Shop id does not exist');
            throw $oEx;
        }

        return oxSpecificShopConfig::getAll();
    }

    /**
     * Get all available module ids
     *
     * @return array
     */
    protected function _getAvailableModuleIds()
    {
        if ($this->_aAvailableModuleIds === null) {
            $oConfig = oxRegistry::getConfig();

            // We are calling getModulesFromDir() because we want to refresh
            // the list of available modules. This is a workaround for OXID
            // bug.
            oxNew('oxModuleList')->getModulesFromDir($oConfig->getModulesDir());

            $this->_aAvailableModuleIds = array_keys($oConfig->getConfigParam('aModulePaths'));
        }

        return $this->_aAvailableModuleIds;
    }
}

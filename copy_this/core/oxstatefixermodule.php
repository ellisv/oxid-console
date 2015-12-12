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
 * State fixer module
 *
 * Extension of regular module class to add module information
 * fixer features
 */
class oxStateFixerModule extends oxModule
{
    /**
     * Fix module states task.
     *
     * Runs version, extend, files, templates, blocks, settings and events
     * information fix tasks.
     *
     * NOTE: This method is no longer used in fix:states command from v1.1.7
     * to v1.2.0. Instead this logic has been transitioned to fix:states
     * command itself. Commands are not overwritable so it makes easier to
     * release patches with changes on them without breaking backwards
     * compatibility.
     *
     * @param oxConfig|null $oConfig If not passed uses default base shop config.
     */
    public function fix(oxConfig $oConfig = null)
    {
        if ($oConfig !== null) {
            $this->setConfig($oConfig);
        }

        $this->fixVersion();
        $this->fixExtend();
        $this->fixFiles();
        $this->fixTemplates();
        $this->fixBlocks();
        $this->fixSettings();
        $this->fixEvents();
    }

    /**
     * Fix module version
     */
    public function fixVersion()
    {
        $sVersion = $this->getInfo('version');
        $this->_addModuleVersion($sVersion, $this->getId());
    }

    /**
     * Fix extension chain of module
     */
    public function fixExtend()
    {
        $aExtend = $this->getInfo('extend');
        $this->_setModuleExtend($this->getId(), $aExtend);
    }

    /**
     * Fix extends without removing used entries.
     *
     * This method is only available from v1.1.7 to v1.2.0 to not break
     * backwards compatibility in a patch.
     */
    public function fixExtendGently()
    {
        $aExtend = $this->getInfo('extend');
        $this->_setModuleExtendGently($this->getId(), $aExtend);
    }

    /**
     * Fix files
     */
    public function fixFiles()
    {
        $aFiles = $this->getInfo('files');
        $this->_addModuleFiles($aFiles, $this->getId());
    }

    /**
     * Fix templates
     */
    public function fixTemplates()
    {
        $aTemplates = $this->getInfo('templates');
        $this->_addTemplateFiles($aTemplates, $this->getId());
    }

    /**
     * Fix blocks
     */
    public function fixBlocks()
    {
        $this->_deleteModuleBlockEntries();

        $aBlocks = $this->getInfo('blocks');
        $this->_addTemplateBlocks($aBlocks, $this->getId());
    }

    /**
     * Delete module block entries
     *
     * @codeCoverageIgnore
     */
    protected function _deleteModuleBlockEntries()
    {
        $sShopId = $this->getConfig()->getShopId();
        $sModuleId = $this->getId();
        $oDb = oxDb::getDb();

        $sSql = 'DELETE FROM oxtplblocks WHERE oxmodule = %s AND oxshopid = %s';
        $oDb->execute(sprintf($sSql, $oDb->quote($sModuleId), $oDb->quote($sShopId)));
    }

    /**
     * Fix settings
     */
    public function fixSettings()
    {
        $this->_deleteModuleSettingEntries();

        $aSettings = $this->getInfo('settings');
        $this->_addModuleSettings($aSettings, $this->getId());
    }

    /**
     * Delete module setting entries
     *
     * @codeCoverageIgnore
     */
    protected function _deleteModuleSettingEntries()
    {
        $sShopId = $this->getConfig()->getShopId();
        $sModuleId = $this->getId();
        $oDb = oxDb::getDb();

        $sSql = 'DELETE FROM oxconfig WHERE oxmodule = %s AND oxshopid = %s';
        $oDb->execute(sprintf($sSql, $oDb->quote('module' . $sModuleId), $oDb->quote($sShopId)));

        $sSql = 'DELETE FROM oxconfigdisplay WHERE oxcfgmodule = %s';
        $oDb->execute(sprintf($sSql, $oDb->quote($sModuleId)));
    }

    /**
     * Fix events
     */
    public function fixEvents()
    {
        $aEvents = $this->getInfo('events');
        $this->_addModuleEvents($aEvents, $this->getId());
    }

    /**
     * Set template extend to database, do cleanup before.
     *
     * @author Alfonsas Cirtautas
     *
     * @param string $sModuleId     Module ID.
     * @param array  $aModuleExtend Extend data array from metadata.
     */
    protected function _setModuleExtend($sModuleId, $aModuleExtend)
    {
        $aInstalledModules = $this->getAllModules();
        $sModulePath = $this->getModulePath($sModuleId);

        // Remove extended modules by path.
        if ($sModulePath && is_array($aInstalledModules)) {
            foreach ($aInstalledModules as $sClassName => $mModuleName) {
                if (!is_array($mModuleName)) {
                    continue;
                }

                foreach ($mModuleName as $sKey => $sModuleName) {
                    if (strpos($sModuleName, $sModulePath . '/') === 0) {
                        unset($aInstalledModules[$sClassName][$sKey]);
                    }
                }
            }
        }

        $aModules = $this->mergeModuleArrays($aInstalledModules, $aModuleExtend);
        $aModules = $this->buildModuleChains($aModules);

        $oConfig = $this->getConfig();
        $oConfig->setConfigParam('aModules', $aModules);
        $oConfig->saveShopConfVar('aarr', 'aModules', $aModules);
    }

    /**
     * Set template extend to database gently.
     *
     * This is a copy of _setModuleExtend() method with a patch of not removing
     * entries if they are still being used and saving data if something has
     * changed.
     *
     * A copy was necessary because we want to avoid backwards compatibility
     * break in a patch. This is only available from v1.1.7 to v1.2.0.
     *
     * @author Alfonsas Cirtautas
     * @author Keywan Ghadami
     *
     * @param string $sModuleId     Module ID.
     * @param array  $aModuleExtend Extend data array from metadata.
     */
    protected function _setModuleExtendGently($sModuleId, $aModuleExtend)
    {
        $aInstalledModules = $this->getAllModules();
        $sModulePath = $this->getModulePath($sModuleId);

        // Remove extended modules by path.
        if ($sModulePath && is_array($aInstalledModules)) {
            foreach ($aInstalledModules as $sClassName => $mModuleName) {
                if (!is_array($mModuleName)) {
                    continue;
                }

                foreach ($mModuleName as $sKey => $sModuleName) {
                    if (strpos($sModuleName, $sModulePath . '/') !== 0) {
                        continue;
                    }

                    $sExtension = $aInstalledModules[$sClassName][$sKey];
                    if (!in_array($sExtension, $aModuleExtend)) {
                        // Remove the extension from the shop config
                        // if it is not listed in the module anymore.
                        unset($aInstalledModules[$sClassName][$sKey]);
                    }
                }
            }
        }

        $aModules = $this->mergeModuleArrays($aInstalledModules, $aModuleExtend);

        if ($aInstalledModules != $aModules) {
            $aModules = $this->buildModuleChains($aModules);
            $oConfig  = $this->getConfig();

            $oConfig->setConfigParam('aModules', $aModules);
            $oConfig->saveShopConfVar('aarr', 'aModules', $aModules);
        }
    }
}
